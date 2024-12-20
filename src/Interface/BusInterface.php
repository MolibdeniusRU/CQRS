<?php

namespace molibdenius\CQRS\Interface;


interface BusInterface
{
    public function registerHandler(string $actionClass, HandlerInterface $handler): void;
    public function dispatch(ActionInterface $action): mixed;
}