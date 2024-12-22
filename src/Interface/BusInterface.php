<?php

namespace molibdenius\CQRS\Interface;



use ReflectionClass;

interface BusInterface
{
    /**
     * @param ReflectionClass $handlerReflection
     * @return void
     */
    public function registerHandler(ReflectionClass $handlerReflection): void;
    public function dispatch(ActionInterface $action): mixed;
}