<?php declare(strict_types=1);

namespace Molibdenius\CQRS\Event;

use Molibdenius\CQRS\Event\Attribute\WithActionTrait;
use Molibdenius\CQRS\Event\Attribute\WithErrorTrait;
use Spiral\RoadRunner\Jobs\Task\ReceivedTaskInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @property ReceivedTaskInterface $initiator
 */
final class NewTaskEvent extends Event
{
    use WithErrorTrait;
    use WithActionTrait;

    public function getTask(): ReceivedTaskInterface
    {
        return $this->initiator;
    }
}
