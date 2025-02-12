<?php

namespace molibdenius\CQRS\Handler\Attribute;

use Attribute;
use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Action\Enum\ActionType;
use molibdenius\CQRS\Action\Enum\PayloadType;

#[Attribute]
class AsQueryHandler extends ActionHandler
{
    /**
     * @param class-string<Action> $queryClass
     * @param PayloadType[] $payloadTypes
     */
    public function __construct(string $queryClass, array $payloadTypes)
    {
        parent::__construct(
            actionClass: $queryClass,
            payloadTypes: $payloadTypes,
            type: ActionType::Query
        );
    }
}