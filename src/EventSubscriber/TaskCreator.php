<?php

declare(strict_types=1);

namespace Molibdenius\CQRS\EventSubscriber;

use Molibdenius\CQRS\Exception\EventException;
use Molibdenius\CQRS\Registry\ActionType;
use Molibdenius\CQRS\Result\ProcessInfo;
use Molibdenius\CQRS\Event\Events;
use Molibdenius\CQRS\Event\NewActionEvent;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\RoadRunner\Jobs\JobsInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class TaskCreator implements EventSubscriberInterface
{
    public function __construct(
        private JobsInterface       $jobs,
        private SerializerInterface $serializer
    )
    {
    }

    public function createTask(NewActionEvent $event): void
    {
        if (!$event->getInitiator() instanceof ServerRequestInterface) {
            return;
        }

        try {
            if (!$event->hasAction() || !$event->hasConfig()) {
                throw new EventException('Event has no action or no config');
            }

            $config = $event->getConfig();
            if ($config->type !== ActionType::Command) {
                return;
            }

            $queue = $this->jobs->connect(ActionType::Command->value);
            $action = $event->getAction();

            $task = $queue->create(
                $config->name ?? $action::class,
                $this->serializer->serialize($action, 'json')
            )
                ->withHeader('action_class', $action::class);

            $task = $queue->dispatch($task);

            $event->setResult(new ProcessInfo(
                name: $config->name ?? $action::class,
                message: $config->asyncMessage ?? 'Command accepted in processing',
                taskId: $task->getId()
            ));

        } catch (\Throwable $exception) {
            $event->setError($exception);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::NewAction->value => ['createTask', 10],
        ];
    }
}
