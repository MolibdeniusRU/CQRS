<?php

declare(strict_types=1);

namespace Molibdenius\CQRS\Event;

use Molibdenius\CQRS\Event\Attribute\WithErrorTrait;
use Molibdenius\CQRS\Event\Attribute\WithResponseTrait;
use Molibdenius\CQRS\Event\Attribute\WithResultTrait;
use Symfony\Contracts\EventDispatcher\Event;

final class ResponseEvent extends Event
{
    use WithErrorTrait;
    use WithResultTrait;
    use WithResponseTrait;
}
