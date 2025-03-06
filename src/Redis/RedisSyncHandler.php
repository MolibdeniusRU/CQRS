<?php

namespace molibdenius\CQRS\Redis;

use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Handler\Attribute\AsSyncHandler;
use molibdenius\CQRS\Handler\Handler;

#[AsSyncHandler(RedisSync::class)]
final readonly class RedisSyncHandler implements Handler
{
    public function __construct(private RedisSynchronizer $synchronizer)
    {
    }

    /**
     * @param RedisSync $action
     */
    public function handle(Action $action): bool
    {
        if ($action->entity === null) {
            return false;
        }

        return $this->synchronizer->synchronize($action->entity);
    }
}