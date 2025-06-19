<?php

namespace Molibdenius\CQRS\Registry;

use Molibdenius\CQRS\Action;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class HandlerConfig
{
    /**
     * @param class-string<Action> $action
     */
    public function __construct(public string $action)
    {
    }
}