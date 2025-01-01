<?php

namespace molibdenius\CQRS\Dispatcher;

use molibdenius\CQRS\ActionBus;
use molibdenius\CQRS\RoadRunnerMode;
use Spiral\RoadRunner\EnvironmentInterface;
use Spiral\RoadRunner\Jobs\ConsumerInterface;

final class QueueDispatcher implements Dispatcher
{
    public function __construct(
        private readonly ConsumerInterface $consumer,
        private readonly ActionBus $bus,
    )
    {
    }
    public function canServe(EnvironmentInterface $env): bool
    {
        return $env->getMode() === RoadRunnerMode::Jobs->value;
    }

    public function serve(): void
    {
        while ($task = $this->consumer->waitTask()) {
            try {
                $action = unserialize($task->getPayload(), ['allowed_classes' => true]);

                $this->bus->dispatch($action);
                // Complete task.
                $task->ack();
            } catch (\Throwable $e) {
                $task->nack($e);
            }
        }
    }
}