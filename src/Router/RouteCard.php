<?php

namespace molibdenius\CQRS\Router;

use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Action\Enum\PayloadType;

final readonly class RouteCard
{
    /**
     * @param string $path
     * @param HttpMethod $method
     * @param PayloadType $payloadType
     * @param class-string<Action> $action
     * @param string|null $name
     */
    public function __construct(
        public string      $path,
        public HttpMethod  $method,
        public PayloadType $payloadType,
        public string      $action,
        public ?string     $name = null,
    )
    {
    }
}