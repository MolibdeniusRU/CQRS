<?php

namespace molibdenius\CQRS;

use molibdenius\CQRS\Interface\ActionInterface;
use molibdenius\CQRS\Interface\BusInterface;
use molibdenius\CQRS\Interface\HandlerInterface;
use RuntimeException;

class ActionBus implements BusInterface
{
    /** @var HandlerInterface[] */
    private array $handlers = [];

    public function registerHandler(string $actionClass, HandlerInterface $handler): void
    {
        $this->handlers[$actionClass] = $handler;
    }

    public function dispatch(ActionInterface $action): mixed
    {
        $actionClass = get_class($action);
        if (!isset($this->handlers[$actionClass])) {
            throw new RuntimeException(sprintf('Action "%s" does not exist.', $actionClass), 400);
        }

        return $this->handlers[$actionClass]->handle($action);
    }
}