<?php

declare(strict_types=1);

namespace Molibdenius\CQRS\EventSubscriber;

use Molibdenius\CQRS\Exception\EventException;
use Molibdenius\CQRS\Registry\ActionType;
use Molibdenius\CQRS\ActionBus\ActionBusInterface;
use Molibdenius\CQRS\Event\NewActionEvent;
use Molibdenius\CQRS\Event\Events;
use Molibdenius\CQRS\Result\ActionResult;
use Molibdenius\CQRS\Result\Result;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ActionExecutor implements EventSubscriberInterface
{
    public function __construct(private ActionBusInterface $actionBus)
    {
    }

    public function executeQuery(NewActionEvent $event): void
    {
        if (!$event->getInitiator() instanceof ServerRequestInterface) {
            return;
        }

        try {
            if (!$event->hasAction() || !$event->hasConfig()) {
                throw new EventException('Event has no action or no config');
            }

            if ($event->getConfig()->type !== ActionType::Query) {
                return;
            }

            $result = $this->actionBus->dispatch($event->getAction());

            if ($result instanceof Result) {
                $event->setResult($result);
                return;
            }

            $event->setResult(new ActionResult(content: $result));


        } catch (\Throwable $exception) {
            $event->setError($exception);
        }

    }

    public function executeCommand(NewActionEvent $event): void
    {
        if (!$event->getInitiator() instanceof ReceivedTaskInterface) {
            return;
        }

        try {
            if (!$event->hasAction() || !$event->hasConfig()) {
                throw new EventException('Event has no action or no config');
            }

            if ($event->getConfig()->type !== ActionType::Command) {
                return;
            }

            $this->actionBus->dispatch($event->getAction());

        } catch (\Throwable $exception) {
            $event->setError($exception);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::NewAction->value => [
                ['executeQuery', 0],
                ['executeCommand', 0],
            ]
        ];
    }
}
