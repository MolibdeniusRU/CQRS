<?php

namespace molibdenius\CQRS\Attribute;

use Attribute;
use molibdenius\CQRS\Enum\ActionType;

#[Attribute]
class CommandHandler extends ActionHandler
{
    public function __construct(string $commandClass, string $path, string $method, string $payloadType)
    {
        parent::__construct(
            actionClass: $commandClass,
            path: $path,
            method: $method,
            payloadType: $payloadType,
            type: ActionType::COMMAND->value
        );
    }
}