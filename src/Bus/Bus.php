<?php

namespace molibdenius\CQRS\Bus;

use molibdenius\CQRS\Action\Action;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;

interface Bus
{
    /** @param ReflectionClass<Action> $handlerReflection */
    public function registerHandler(ReflectionClass $handlerReflection): void;

    public function dispatch(Action $action): mixed;

    public function resolveAction(ServerRequestInterface $request): Action;
}