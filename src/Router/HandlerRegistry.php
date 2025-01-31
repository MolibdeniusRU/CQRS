<?php

namespace molibdenius\CQRS\Router;

use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Action\Enum\ActionType;
use molibdenius\CQRS\Handler\Handler;

final readonly class HandlerRegistry
{
    /**
     * @param class-string $action
     * @param ActionType $type
     * @param class-string $handler
     */
    public function __construct(
        public string     $action,
        public ActionType $type,
        public string     $handler,
    )
    {
    }
}