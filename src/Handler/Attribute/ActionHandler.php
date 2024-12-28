<?php

namespace molibdenius\CQRS\Handler\Attribute;

use Attribute;
use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Action\Enum\ActionType;
use molibdenius\CQRS\Action\Enum\PayloadType;
use molibdenius\CQRS\Router\HttpMethod;

#[Attribute]
class ActionHandler
{
    /**
     * @param class-string<Action> $actionClass
     * @param string $path
     * @param HttpMethod $method
     * @param PayloadType $payloadType
     * @param ActionType $type
     */
    public function __construct(
        public string $actionClass,
        public string $path,
        public HttpMethod $method,
        public PayloadType $payloadType,
        public ActionType $type
    )
    {
    }
}