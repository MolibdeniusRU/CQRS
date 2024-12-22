<?php

namespace molibdenius\CQRS\Attribute;

use Attribute;
use molibdenius\CQRS\Enum\ActionType;

#[Attribute]
class QueryHandler extends ActionHandler
{
    public function __construct(string $queryClass, string $path, string $method, string $payloadType)
    {
        parent::__construct(
            actionClass:  $queryClass,
            path: $path,
            method: $method,
            payloadType: $payloadType,
            type: ActionType::Query->value
        );
    }
}