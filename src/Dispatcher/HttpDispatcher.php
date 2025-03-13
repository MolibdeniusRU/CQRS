<?php

namespace molibdenius\CQRS\Dispatcher;

use Exception;
use JsonException;
use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Action\Enum\ActionType;
use molibdenius\CQRS\Bus\ActionBusInterface;
use molibdenius\CQRS\Extractor\Extractor;
use molibdenius\CQRS\Extractor\ExtractorFactory;
use molibdenius\CQRS\RoadRunnerMode;
use molibdenius\CQRS\Router\Router;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Spiral\RoadRunner\EnvironmentInterface;
use Spiral\RoadRunner\Http\PSR7WorkerInterface;
use Spiral\RoadRunner\Jobs\Exception\JobsException;
use Spiral\RoadRunner\Jobs\JobsInterface;
use Throwable;

final readonly class HttpDispatcher implements DispatcherInterface
{
    public function __construct(
        private PSR7WorkerInterface $worker,
        private JobsInterface       $jobs,
        private ActionBusInterface $actionBus,
        private Router              $router,
    )
    {
    }

    private function init(): void
    {
        $this->router->setResource($this->actionBus->getMetadataMap()->getHttpHandlers());
    }

    public function canServe(EnvironmentInterface $env): bool
    {
        return $env->getMode() === RoadRunnerMode::Http->value;
    }

    public function serve(): void
    {
        $this->init();

        while (true) {
            try {
                $request = $this->worker->waitRequest();
                if ($request === null) {
                    break;
                }

                $routeParams = $this->router->resolveRouteParams($request);

                if (!isset($routeParams['_action'])) {
                    throw new RuntimeException(sprintf("On route %s action does not exist.", $request->getUri()->getPath()));
                }

                /** @var class-string<Action> $actionClass */
                $actionClass = $routeParams['_action'];

                $action = $this->actionBus->resolveAction($actionClass);
                $action->load($routeParams);

                $payload = $this->getPayloadExtractor($request)->extract();

                if (!empty($payload)) {
                    $action->load($payload);
                }

                match ($action->getActionType()) {
                    ActionType::Command => $this->dispatchAsCommand($action),
                    ActionType::Query => $this->dispatchAsQuery($action),
                };
            } catch (Throwable $e) {
                $this->worker->respond(
                    new Response(
                        400,
                        ['Content-Type' => 'application/json'],
                        json_encode([
                            'message' => $e->getMessage(),
                            'trace' => $e->getTrace(),
                        ], JSON_THROW_ON_ERROR),
                    )
                );
            }
        }
    }

    /**
     * @throws JobsException
     * @throws JsonException
     * @throws Exception
     */
    private function dispatchAsCommand(Action $action): void
    {
        $queue = $this->jobs->connect($action->getActionType()->value);

        $task = $queue->create($action->getActionType()->value, serialize($action));
        $task = $queue->dispatch($task);

        $this->worker->respond(new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode([
                'message' => 'Your command has been accepted for processing',
                'task_id' => $task->getId()
            ], JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * @throws JsonException
     */
    private function dispatchAsQuery(Action $action): void
    {
        $result = $this->actionBus->dispatch($action);

        $this->worker->respond(new Response(
            200,
            ['Content-Type' => 'application/json'],
            is_string($result) ? $result : json_encode($result, JSON_THROW_ON_ERROR),
        ));
    }

    private function getPayloadExtractor(ServerRequestInterface $request): Extractor
    {
        return ExtractorFactory::createExtractor('http.payload.extractor', $request);
    }
}