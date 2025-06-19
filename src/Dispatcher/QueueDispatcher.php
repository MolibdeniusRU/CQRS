<?php

declare(strict_types=1);

namespace Molibdenius\CQRS\Dispatcher;

use Molibdenius\CQRS\EventLoop\EventLoopInterface;
use Spiral\RoadRunner\Jobs\ConsumerInterface;
use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;
use Throwable;

final readonly class QueueDispatcher implements DispatcherInterface
{
    /**
     * @param EventLoopInterface<ReceivedTaskInterface, void> $eventLoop
     */
    public function __construct(
        private ConsumerInterface  $consumer,
        private EventLoopInterface $eventLoop,
    )
    {
    }

    public function serve(): void
    {
        while ($task = $this->consumer->waitTask()) {
            try {
                $this->eventLoop->run($task);
            } catch (Throwable $e) {
                $task->nack($e);
            }
        }
    }
}
