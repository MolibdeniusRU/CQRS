<?php

namespace molibdenius\CQRS\Handler\Attribute;

use Attribute;
use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Action\Enum\ActionType;
use molibdenius\CQRS\Metadata\HandlerMetadata;

#[Attribute]
class AsSyncHandler extends HandlerMetadata
{
    /**
     * @param class-string<Action> $sync
     */
    public function __construct(string $sync)
    {
        parent::__construct(
            actionClass: $sync,
            type: ActionType::Sync,
        );
    }
}