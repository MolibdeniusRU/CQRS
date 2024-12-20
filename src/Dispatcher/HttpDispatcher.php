<?php

namespace molibdenius\CQRS\Dispatcher;

use JsonException;
use molibdenius\CQRS\Attribute\ActionHandler;
use molibdenius\CQRS\Enum\ActionState;
use molibdenius\CQRS\Enum\ActionType;
use molibdenius\CQRS\Enum\PayloadType;
use molibdenius\CQRS\Enum\RoadRunnerMode;
use molibdenius\CQRS\Interface\ActionInterface;
use molibdenius\CQRS\Interface\BusInterface;
use molibdenius\CQRS\Interface\DispatcherInterface;
use molibdenius\CQRS\Router;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner\EnvironmentInterface;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Jobs\Exception\JobsException;
use Spiral\RoadRunner\Jobs\Jobs;
use Spiral\RoadRunner\Jobs\QueueInterface;
use Spiral\RoadRunner\Worker;
use Throwable;
use WS\Utils\Collections\ArrayList;

/** @property Router $router */
final class HttpDispatcher implements DispatcherInterface
{
    private Router $router;

    private BusInterface $bus;

    private EnvironmentInterface $env;

    private PSR7Worker $worker;

    /** @var QueueInterface[] */
    private array $queues;

    public function canServe(EnvironmentInterface $env): bool
    {
        $this->env = $env;
        return $this->env->getMode() === RoadRunnerMode::Http->value;
    }

    public function serve(): void
    {
        $factory = new Psr17Factory();
        $this->worker = new PSR7Worker(Worker::create(), $factory, $factory, $factory);
        $jobs = new Jobs(RPC::create($this->env->getRPCAddress()));

        $this->queues[ActionType::COMMAND->value] = $jobs->connect(ActionType::COMMAND->value);
        $this->queues[ActionType::QUERY->value] = $jobs->connect(ActionType::QUERY->value);

        while (true) {
            try {
                $request = $this->worker->waitRequest();
                if ($request === null) {
                    break;
                }

                $this->router->setRequest($request);

                $handlerClass = $this->router->resolveHandler();
                $action = null;

                $attributes = new ArrayList((new ReflectionClass($handlerClass))->getAttributes());
                $attributes
                    ->stream()
                    ->filter(function (\ReflectionAttribute $attributeClass) {
                            return $attributeClass->newInstance() instanceof ActionHandler;
                        },
                    )
                    ->map(function (\ReflectionAttribute $attributeClass) use (&$action) {
                            $attribute = $attributeClass->newInstance();
                            /** @var ActionInterface $action */
                            $action = new $attribute->actionClass();
                            $action->setType($attribute->type);
                            $action->setState(ActionState::NEW->value);
                            $action->setPayloadType($attribute->payloadType);
                    });

                $payload = $this->getPayload($request, $action->getPayloadType());
                if ($payload !== null) {
                    $action->load($payload);
                }

                match ($action->getType()) {
                    ActionType::COMMAND->value => $this->handleAsCommand($action),
                    ActionType::QUERY->value => $this->handleAsQuery($action),
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
    private function handleAsCommand(ActionInterface $action): void
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
    private function handleAsQuery(ActionInterface $action): void
    {
        $result = $this->bus->dispatch($action);

        $this->worker->respond(new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($result, JSON_THROW_ON_ERROR),
        ));
    }

    /**
     * @return mixed[]|null
     */
    private function getPayload(ServerRequestInterface $request, ?string $payloadType): array|null
    {
        return match ($payloadType) {
            PayloadType::QUERY->value =>$request->getQueryParams(),
            PayloadType::BODY->value =>$request->getParsedBody(),
            default => null,
        };
    }

    public function getBus(): BusInterface
    {
        return $this->bus;
    }

    public function setBus(BusInterface $bus): void
    {
        $this->bus = $bus;
    }

    public function setRouter(Router $router): void
    {
        $this->router = $router;
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

}