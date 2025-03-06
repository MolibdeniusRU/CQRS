<?php

namespace molibdenius\CQRS\Metadata;

use Attribute;
use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Action\Enum\ActionType;

#[Attribute]
class HandlerMetadata
{
    /**
     * @param class-string<Action> $actionClass
     */
    public function __construct(
        public string      $actionClass,
        public ActionType $type,
    )
    {
    }
}