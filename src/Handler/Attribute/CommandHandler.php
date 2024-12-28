<?php

namespace molibdenius\CQRS\Handler\Attribute;

use Attribute;
use molibdenius\CQRS\Action\Enum\ActionType;
use molibdenius\CQRS\Action\Enum\PayloadType;
use molibdenius\CQRS\Router\HttpMethod;

#[Attribute]
class CommandHandler extends ActionHandler
{
    public function __construct(string $commandClass, string $path, HttpMethod $method, PayloadType $payloadType)
    {
        parent::__construct(
            actionClass: $commandClass,
            path: $path,
            method: $method,
            payloadType: $payloadType,
            type: ActionType::Command
        );
    }
}