<?php

declare(strict_types=1);

namespace Molibdenius\CQRS\Event;

use Molibdenius\CQRS\Event\Attribute\WithDebugTrait;
use Molibdenius\CQRS\Event\Attribute\WithResponseTrait;
use Symfony\Contracts\EventDispatcher\Event;
use Throwable;

/**
 * @property  Throwable $initiator
 */
final class HttpErrorEvent extends Event
{
    use WithResponseTrait;
    use WithDebugTrait;

    public function getError(): Throwable
    {
        return $this->initiator;
    }
}
