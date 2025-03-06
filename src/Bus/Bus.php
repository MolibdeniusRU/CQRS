<?php

namespace molibdenius\CQRS\Bus;

use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Handler\Handler;
use molibdenius\CQRS\Metadata\MetadataMap;

interface Bus
{
    /**
     * @param class-string<Handler>[] $handlers
     */
    public function registerHandlers(array $handlers): void;

    public function dispatch(Action $action): mixed;

    /**
     * @param class-string<Action> $actionClass
     * @param mixed[] $payloads
     */
    public function resolveAction(string $actionClass, array $payloads = []): Action;

    public function getMetadataMap(): MetadataMap;

}