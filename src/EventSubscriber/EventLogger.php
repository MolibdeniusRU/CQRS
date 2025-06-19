<?php

declare(strict_types=1);

namespace Molibdenius\CQRS\EventSubscriber;

use Molibdenius\CQRS\Event\Events;
use Molibdenius\CQRS\Event\HttpErrorEvent;
use Molibdenius\CQRS\Event\NewActionEvent;
use Molibdenius\CQRS\Event\NewTaskEvent;
use Molibdenius\CQRS\Event\RetryTaskEvent;
use Molibdenius\CQRS\Event\RequestEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class EventLogger implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function logRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $this->logger->debug(vsprintf(Events::getLogView(Events::Request), [
            $request->getMethod(),
            $request->getUri()->getAuthority()
        ]));
    }

    public function logNewAction(NewActionEvent $event): void
    {
        $log = [
            'unknown',
            'unknown'
        ];

        if ($event->hasAction() && $event->hasConfig()) {
            $log = [
                $event->getAction()::class,
                $event->getConfig()->type->value
            ];
        }

        $this->logger->debug(vsprintf(Events::getLogView(Events::NewAction), $log));
    }

    public function logHttpError(HttpErrorEvent $event): void
    {
        $this->logger->error(sprintf(Events::getLogView(Events::HttpError), $event->getError()->getMessage()));
    }

    public function logNewTask(NewTaskEvent $event): void
    {
        $log = [$event->getTask()->getName(), $event->getTask()->getId()];

        $this->logger->info(vsprintf(Events::getLogView(Events::NewTask), $log));
    }

    public function logRetryTask(RetryTaskEvent $event): void
    {
        $error = $event->getError()?->getMessage();

        if (null === $error) {
            $error = 'unknown';
        }

        $log = [
            $event->getTask()->getQueue(),
            $event->getTask()->getName(),
            $error
        ];

        $this->logger->error(vsprintf(Events::getLogView(Events::RetryTask), $log));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::Request->value => ['logRequest', 500],
            Events::NewAction->value => ['logNewAction', 500],
            Events::HttpError->value => ['logHttpError', 500],
            Events::NewTask->value => ['logNewTask', 500],
            Events::RetryTask->value => ['logRetryTask', 500],
        ];
    }
}
