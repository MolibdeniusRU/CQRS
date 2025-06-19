<?php

declare(strict_types=1);

namespace Molibdenius\CQRS\Event;

use Molibdenius\CQRS\Event\Attribute\WithActionTrait;
use Molibdenius\CQRS\Event\Attribute\WithConfigTrait;
use Molibdenius\CQRS\Event\Attribute\WithErrorTrait;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @property  ServerRequestInterface $initiator
 */
final class RequestEvent extends Event
{
    use WithErrorTrait;
    use WithActionTrait;
    use WithConfigTrait;

    public function getRequest(): ServerRequestInterface
    {
        return $this->initiator;
    }
}
