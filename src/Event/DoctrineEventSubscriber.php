<?php

namespace molibdenius\CQRS\Event;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Action\ActionFactory;
use molibdenius\CQRS\Action\Enum\ActionType;
use molibdenius\CQRS\Metadata\MetadataMap;
use RoadRunner\Logger\Logger;
use Spiral\RoadRunner\Jobs\JobsInterface;
use Throwable;

final readonly class DoctrineEventSubscriber implements EventSubscriber
{
    public function __construct(
        private JobsInterface $jobs,
        private MetadataMap   $metadataMap,
        private Logger        $logger
    )
    {
    }

    public function postPersist(PostPersistEventArgs $eventArgs): void
    {
        $this->pushToSynchronize($eventArgs->getObject());
    }

    public function postUpdate(PostUpdateEventArgs $eventArgs): void
    {
        $this->pushToSynchronize($eventArgs->getObject());
    }

    public function postRemove(PostRemoveEventArgs $eventArgs): void
    {
        $this->pushToSynchronize($eventArgs->getObject());
    }

    public function getSubscribedEvents(): array
    {
        return [Events::postPersist, Events::postUpdate, Events::postRemove];
    }

    private function pushToSynchronize(object $entity): void
    {
        $queue = $this->jobs->connect(ActionType::Sync->value);
        try {
            $this->metadataMap->getSyncActions()->stream()
                ->map(function (string $class) use ($queue, $entity) {
                    /** @var class-string<Action> $class */

                    $action = ActionFactory::create(
                        $class,
                        ActionType::Sync,
                        ['entity' => $entity]
                    );

                    $queue->dispatch($queue->create($action->getActionType()->value, serialize($action)));
                });
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}