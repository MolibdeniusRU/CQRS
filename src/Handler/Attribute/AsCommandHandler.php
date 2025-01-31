<?php

namespace molibdenius\CQRS\Handler\Attribute;

use Attribute;
use molibdenius\CQRS\Action\Enum\ActionType;
use molibdenius\CQRS\Action\Enum\PayloadType;
use molibdenius\CQRS\Router\HttpMethod;

#[Attribute]
class AsCommandHandler extends ActionHandler
{
    /**
     * @param class-string $commandClass
     * @param string $path
     * @param HttpMethod $method
     * @param PayloadType $payloadType
     */
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