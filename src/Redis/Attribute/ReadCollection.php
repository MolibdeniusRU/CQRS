<?php

namespace molibdenius\CQRS\Redis\Attribute;

use Attribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
final readonly class ReadCollection
{
    /**
     * @param string $name
     * @param class-string $repository
     * @param string $method
     * @param mixed[] $arguments
     */
    public function __construct(
        public string $name,
        public string $repository,
        public string $method,
        public array  $arguments = []
    )
    {
    }
}