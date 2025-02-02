<?php

namespace molibdenius\CQRS\Handler\Attribute;

use Attribute;
use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Action\Enum\ActionType;
use molibdenius\CQRS\Action\Enum\PayloadType;
use molibdenius\CQRS\Router\HttpMethod;

#[Attribute]
class AsQueryHandler extends ActionHandler
{
    /**
     * @param class-string<Action> $queryClass
     * @param string $path
     * @param HttpMethod $method
     * @param PayloadType $payloadType
     */
    public function __construct(string $queryClass, string $path, HttpMethod $method, PayloadType $payloadType)
    {
        parent::__construct(
            actionClass: $queryClass,
            path: $path,
            method: $method,
            payloadType: $payloadType,
            type: ActionType::Query
        );
    }
}