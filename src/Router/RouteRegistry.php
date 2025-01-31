<?php

namespace molibdenius\CQRS\Router;

use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Action\Enum\PayloadType;

final readonly class RouteRegistry
{
    /**
     * @param string $path
     * @param HttpMethod $method
     * @param PayloadType $payloadType
     * @param class-string $action
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