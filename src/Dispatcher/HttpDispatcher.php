<?php

namespace molibdenius\CQRS\Dispatcher;

use Exception;
use JsonException;
use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Action\Enum\ActionType;
use molibdenius\CQRS\Action\Enum\PayloadType;
use molibdenius\CQRS\Bus\Bus;
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
use Spiral\RoadRunner\Jobs\QueueInterface;
use Throwable;
use WS\Utils\Collections\HashMap;

final class HttpDispatcher implements Dispatcher
{

    /** @var HashMap<QueueInterface> */
    private HashMap $queues;

    public function __construct(
        private readonly PSR7WorkerInterface $worker,
        private readonly JobsInterface       $jobs,
        private readonly Bus                 $bus,
        private readonly Router $router,
    )
    {
    }

    private function init(): void
    {
        $this->queues = new HashMap();
        $this->queues->put(ActionType::Command, $this->jobs->connect(ActionType::Command->value));
        $this->queues->put(ActionType::Query, $this->jobs->connect(ActionType::Query->value));
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

                $action = $this->bus->resolveAction($actionClass);
                $action->load($routeParams);

                $payload = $this->getPayloadExtractor($request, $action->getActionPayloadTypes())->extract();

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
        $queue = $this->queues->get($action->getActionType());

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
        $result = $this->bus->dispatch($action);

        $this->worker->respond(new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($result, JSON_THROW_ON_ERROR),
        ));
    }

    /** @param PayloadType[] $payloadTypes */
    private function getPayloadExtractor(ServerRequestInterface $request, array $payloadTypes): Extractor
    {
        return ExtractorFactory::createExtractor('http.payload.extractor', $request, $payloadTypes);
    }
}