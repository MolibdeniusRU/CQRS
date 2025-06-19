<?php

namespace Molibdenius\CQRS\EventLoop;

use Molibdenius\CQRS\Event\Attribute\AttributeBag;
use Molibdenius\CQRS\Event\Events;
use Molibdenius\CQRS\Event\NewActionEvent;
use Molibdenius\CQRS\Event\NewTaskEvent;
use Molibdenius\CQRS\Event\RetryTaskEvent;
use Molibdenius\CQRS\Exception\EventException;
use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

/**
 * @implements EventLoopInterface<ReceivedTaskInterface, void>
 */
final readonly class QueueEventLoop implements EventLoopInterface
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private bool                     $debug = false
    )
    {
    }

    public function run(object $initiator): void
    {
        $task = $initiator;
        try {
            $event = new NewTaskEvent($task, new \WeakMap());
            $this->eventDispatcher->dispatch($event, Events::NewTask->value);

            if (!$event->hasAction()) {
                if ($event->hasError()) {
                    $this->retryTask($event->getError(), $task);
                    return;
                }

                throw new EventException('Event NewTask does not contain Action');
            }

            $event = new NewActionEvent($task, $event->getStorage());

            $this->eventDispatcher->dispatch($event, Events::NewAction->value);

            if ($event->hasError()) {
                $this->retryTask($event->getError(), $task);
                return;
            }

            $task->ack();
        } catch (\Throwable $e) {
            $this->retryTask($e, $task);
        }
    }

    private function retryTask(Throwable $error, ReceivedTaskInterface $task): void
    {
        $event = new RetryTaskEvent($task, new \WeakMap(), [
            AttributeBag::ERROR => $error,
            AttributeBag::DEBUG => $this->debug
        ]);

        $this->eventDispatcher->dispatch($event, Events::RetryTask->value);
    }
}
