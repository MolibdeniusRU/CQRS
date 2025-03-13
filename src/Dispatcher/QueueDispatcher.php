<?php

namespace molibdenius\CQRS\Dispatcher;

use molibdenius\CQRS\Bus\ActionBusInterface;
use molibdenius\CQRS\RoadRunnerMode;
use RoadRunner\Logger\Logger;
use Spiral\RoadRunner\EnvironmentInterface;
use Spiral\RoadRunner\Jobs\ConsumerInterface;

final readonly class QueueDispatcher implements DispatcherInterface
{
    public function __construct(
        private ConsumerInterface  $consumer,
        private ActionBusInterface $bus,
        private Logger             $logger,
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
                $this->logger->error($e->getMessage());
            }
        }
    }
}