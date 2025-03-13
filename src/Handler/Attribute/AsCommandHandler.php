<?php

namespace molibdenius\CQRS\Handler\Attribute;

use Attribute;
use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Action\Enum\ActionType;

#[Attribute]
class AsCommandHandler extends HandlerMetadata
{
    /**
     * @param class-string<Action> $command
     */
    public function __construct(string $command)
    {
        parent::__construct(
            actionClass: $command,
            type: ActionType::Command,
        );
    }
}