<?php

namespace molibdenius\CQRS\Dispatcher;

use JsonException;
use molibdenius\CQRS\ActionBus;
use molibdenius\CQRS\Enum\ActionType;
use molibdenius\CQRS\Enum\PayloadType;
use molibdenius\CQRS\Enum\RoadRunnerMode;
use molibdenius\CQRS\Interface\ActionInterface;
use molibdenius\CQRS\Interface\DispatcherInterface;
use molibdenius\CQRS\Router;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\RoadRunner\EnvironmentInterface;
use Spiral\RoadRunner\Http\PSR7WorkerInterface;
use Spiral\RoadRunner\Jobs\Exception\JobsException;
use Spiral\RoadRunner\Jobs\JobsInterface;
use Spiral\RoadRunner\Jobs\QueueInterface;
use Throwable;

/** @property Router $router */
final class HttpDispatcher implements DispatcherInterface
{
    private ActionBus $bus;

    /** @var QueueInterface[] */
    private array $queues;

    public function __construct(private readonly PSR7WorkerInterface $worker, private readonly JobsInterface $jobs)
    {}

    public function canServe(EnvironmentInterface $env): bool
    {
        return $env->getMode() === RoadRunnerMode::Http->value;
    }

    public function serve(): void
    {
        $this->queues[ActionType::Command->value] = $this->jobs->connect(ActionType::Command->value);
        $this->queues[ActionType::Query->value] = $this->jobs->connect(ActionType::Query->value);

        while (true) {
            try {
                $request = $this->worker->waitRequest();
                if ($request === null) {
                    break;
                }

                $action = $this->bus->resolveAction($request);

                $payload = $this->getPayload($request, $action->getPayloadType());
                if ($payload !== null) {
                    $action->load($payload);
                }

                match ($action->getType()) {
                    ActionType::Command->value => $this->dispatchAsCommand($action),
                    ActionType::Query->value => $this->dispatchAsQuery($action),
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
     */
    private function dispatchAsCommand(ActionInterface $action): void
    {
        $task = $this->queues[$action->getType()]
            ->create(
                name: $action->getType(),
                payload: serialize($action),
            );
        $task = $this->queues[$action->getType()]->dispatch($task);

        $this->worker->respond(new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode(['task_id' => $task->getId()], JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * @throws JsonException
     */
    private function dispatchAsQuery(ActionInterface $action): void
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
    private function getPayload(ServerRequestInterface $request, ?string $payloadType): array|null
    {
        return match ($payloadType) {
            PayloadType::Query->value =>$request->getQueryParams(),
            PayloadType::Body->value =>$request->getParsedBody(),
            default => null,
        };
    }

    public function getBus(): ActionBus
    {
        return $this->bus;
    }

    public function setBus(ActionBus $bus): void
    {
        $this->bus = $bus;
    }
}