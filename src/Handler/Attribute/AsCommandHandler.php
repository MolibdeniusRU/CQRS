<?php

namespace molibdenius\CQRS\Handler\Attribute;

use Attribute;
use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Action\Enum\ActionType;
use molibdenius\CQRS\Action\Enum\PayloadType;

#[Attribute]
class AsCommandHandler extends ActionHandler
{
    /**
     * @param class-string<Action> $commandClass
     * @param PayloadType[] $payloadTypes
     */
    public function __construct(string $commandClass, array $payloadTypes)
    {
        parent::__construct(
            actionClass: $commandClass,
            payloadTypes: $payloadTypes,
            type: ActionType::Command
        );
    }
}