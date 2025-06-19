<?php

declare(strict_types=1);

namespace Molibdenius\CQRS\Event;

use Molibdenius\CQRS\Event\Attribute\WithActionTrait;
use Molibdenius\CQRS\Event\Attribute\WithConfigTrait;
use Molibdenius\CQRS\Event\Attribute\WithErrorTrait;
use Molibdenius\CQRS\Event\Attribute\WithResultTrait;
use Symfony\Contracts\EventDispatcher\Event;

final class NewActionEvent extends Event
{
    use WithErrorTrait;
    use WithResultTrait;
    use WithActionTrait;
    use WithConfigTrait;
}
