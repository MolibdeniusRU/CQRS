<?php

namespace molibdenius\CQRS\Handler\Attribute;

use Attribute;
use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Action\Enum\ActionType;

#[Attribute]
class AsQueryHandler extends HandlerMetadata
{
    /**
     * @param class-string<Action> $query
     */
    public function __construct(string $query)
    {
        parent::__construct(
            actionClass: $query,
            type: ActionType::Query,
        );
    }
}