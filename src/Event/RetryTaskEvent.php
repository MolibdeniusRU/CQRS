<?php

declare(strict_types=1);

namespace Molibdenius\CQRS\Event;

use Molibdenius\CQRS\Event\Attribute\WithAttributeTrait;
use Molibdenius\CQRS\Event\Attribute\WithConfigTrait;
use Molibdenius\CQRS\Event\Attribute\WithDebugTrait;
use Molibdenius\CQRS\Event\Attribute\WithErrorTrait;
use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @property ReceivedTaskInterface $initiator
 */
final class RetryTaskEvent extends Event
{
    use WithAttributeTrait;
    use WithDebugTrait;
    use WithErrorTrait;
    use WithConfigTrait;

    public function getTask(): ReceivedTaskInterface
    {
        return $this->initiator;
    }
}
