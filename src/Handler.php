<?php

declare(strict_types=1);

namespace Molibdenius\CQRS;

/**
 * @template T
 */
interface Handler
{
    /**
     *
     * @implements Action<T>
     *
     * @param Action<T> $action
     */
    public function handle(Action $action): mixed;
}
