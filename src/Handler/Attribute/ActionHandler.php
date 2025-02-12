<?php

namespace molibdenius\CQRS\Handler\Attribute;

use Attribute;
use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Action\Enum\ActionType;
use molibdenius\CQRS\Action\Enum\PayloadType;

#[Attribute]
class ActionHandler
{
    /**
     * @param class-string<Action> $actionClass
     * @param PayloadType[] $payloadTypes
     */
    public function __construct(
        public string      $actionClass,
        public array $payloadTypes,
        public ActionType  $type
    )
    {
    }
}