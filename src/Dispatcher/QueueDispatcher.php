<?php

namespace molibdenius\CQRS\Dispatcher;

use molibdenius\CQRS\Enum\RoadRunnerMode;
use molibdenius\CQRS\Interface\BusInterface;
use molibdenius\CQRS\Interface\DispatcherInterface;
use Spiral\RoadRunner\EnvironmentInterface;
use Spiral\RoadRunner\Jobs\Consumer;
use Spiral\RoadRunner\Jobs\Exception\ReceivedTaskException;
use Spiral\RoadRunner\Jobs\Exception\SerializationException;

final class QueueDispatcher implements DispatcherInterface
{

    public BusInterface $bus;
    public function canServe(EnvironmentInterface $env): bool
    {
        return $env->getMode() === RoadRunnerMode::Jobs->value;
    }

    /**
     * @throws SerializationException
     * @throws ReceivedTaskException
     */
    public function serve(): void
    {
        $consumer = new Consumer();

        while ($task = $consumer->waitTask()) {
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


    public function getBus(): BusInterface
    {
        return $this->bus;
    }

    public function setBus(BusInterface $bus): void
    {
        $this->bus = $bus;
    }


}