<?php

declare(strict_types=1);

namespace Molibdenius\CQRS\Registry;

use Molibdenius\CQRS\Exception\RegistryException;
use Throwable;

final readonly class ActionRetry
{
    public const int DEFAULT_DELAY = 0;

    public const int DEFAULT_ATTEMPTS = 1;

    /**
     * @param class-string<Throwable> $exception
     * @param int<0, max> $delay
     *
     * @throws RegistryException
     */
    public function __construct(
        public string $exception,
        public int    $attempts = self::DEFAULT_ATTEMPTS,
        public int    $delay = self::DEFAULT_DELAY
    )
    {
        if ($this->attempts < 1) {
            throw new RegistryException('attempts must be greater than 0');
        }
    }
}
