<?php

namespace molibdenius\CQRS\Dispatcher;

use Exception;
use JsonException;
use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Action\Enum\ActionType;
use molibdenius\CQRS\Action\Enum\PayloadType;
use molibdenius\CQRS\ActionBus;
use molibdenius\CQRS\RoadRunnerMode;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
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
        private readonly ActionBus           $bus,
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

                $action = $this->bus->resolveAction($request);

                $payload = $this->extractPayload($request, $action->getPayloadType());

                if ($payload !== null) {
                    $action->load($payload);
                }

                match ($action->getType()) {
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
                    ));
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
        $queue = $this->queues->get($action->getType());

        $task = $queue->create($action->getType(), serialize($action));
        $task = $queue->dispatch($task);

        $this->worker->respond(new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode(['task_id' => $task->getId()], JSON_THROW_ON_ERROR),
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

    /**
     * @return array<string, mixed>|null
     */
    private function extractPayload(ServerRequestInterface $request, ?PayloadType $payloadType): array|null
    {
        return match ($payloadType) {
            PayloadType::Query => $request->getQueryParams(),
            PayloadType::Body => $request->getParsedBody(),
            default => null,
        };
    }
}