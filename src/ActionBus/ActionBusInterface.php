<?php declare(strict_types=1);

namespace Molibdenius\CQRS\ActionBus;

use Molibdenius\CQRS\Action;

interface ActionBusInterface
{
    public function dispatch(Action $action): mixed;

}
