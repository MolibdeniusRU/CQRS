<?php

namespace molibdenius\CQRS\Bus;

use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Router\Router;
use Symfony\Component\DependencyInjection\Definition;
use WS\Utils\Collections\Collection;

interface Bus
{
    /**
     * @param Collection<Definition> $definitions
     */
    public function registerHandlers(Collection $definitions, Router $router): void;

    public function dispatch(Action $action): mixed;

    /**
     * @param class-string<Action> $actionClass
     */
    public function resolveAction(string $actionClass): Action;
}