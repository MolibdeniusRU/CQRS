<?php

declare(strict_types=1);

namespace Molibdenius\CQRS\EventSubscriber;

use Molibdenius\CQRS\Event\Events;
use Molibdenius\CQRS\Event\RetryTaskEvent;
use Molibdenius\CQRS\Exception\EventException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class TaskRetryer implements EventSubscriberInterface
{
    /**
     * @throws EventException
     */
    public function retryTask(RetryTaskEvent $event): void
    {
        $error = $event->getError();
        $task = $event->getTask();
        $config = $event->getConfig();

        if (null === $config || null === $error) {
            throw new EventException('Event has no action or no config');
        }

        if (!empty($config->retries)) {
            $retry = $config->retries[$error::class] ?? null;

            if ($retry === null) {
                $task->nack($error);
                return;
            }

            if ($task->hasHeader('attempts')) {
                $attempts = (int)$task->getHeaderLine('attempts');
                if ($attempts <= 0) {
                    $task->nack($error);
                    return;
                }

                $task = $task->withHeader('attempts', (string)($attempts - 1));
                if (method_exists($task, 'withDelay')) {
                    $task = $task->withDelay($retry->delay);
                }
                $task->requeue($error);
                return;
            }

            $task = $task->withHeader('attempts', (string)$retry->attempts);
            if (method_exists($task, 'withDelay')) {
                $task = $task->withDelay($retry->delay);
            }
            $task->requeue($error);
            return;
        }

        $task->nack($error);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::RetryTask->value => ['retryTask', 0],
        ];
    }
}
