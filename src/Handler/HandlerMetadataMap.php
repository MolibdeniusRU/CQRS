<?php

namespace molibdenius\CQRS\Handler;

use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Handler\Attribute\HandlerMetadata;
use WS\Utils\Collections\Collection;
use WS\Utils\Collections\CollectionFactory;
use WS\Utils\Collections\Map;
use WS\Utils\Collections\MapFactory;

class HandlerMetadataMap
{
    private Map $map;

    private Collection $httpHandlers;

    private Collection $syncActions;

    public function __construct()
    {
        $this->map = MapFactory::emptyObject();
        $this->httpHandlers = CollectionFactory::empty();
        $this->syncActions = CollectionFactory::empty();
    }

    /**
     * @param class-string<Action> $action
     * @return class-string<Handler>
     */
    public function getHandler(string $action): string
    {
        return $this->map->get($action)[0];
    }


    /**
     * @param class-string<Action> $action
     * @param class-string<Handler> $handler
     */
    public function setMetadata(string $action, string $handler, HandlerMetadata $metadata): self
    {
        $this->map->put($action, [$handler, $metadata]);

        return $this;
    }

    /**
     * @param class-string<Action> $action
     */
    public function getMetadata(string $action): HandlerMetadata
    {
        return $this->map->get($action)[1];
    }

    /**
     * @return Collection<class-string<Handler>>
     */
    public function getHttpHandlers(): Collection
    {
        return $this->httpHandlers;
    }

    /**
     * @param class-string<Handler>[] $httpHandlers
     */
    public function setHttpHandlers(array $httpHandlers): self
    {
        $this->httpHandlers = CollectionFactory::from($httpHandlers);

        return $this;
    }

    /**
     * @param class-string<Handler> $httpHandler
     */
    public function addHttpHandler(string $httpHandler): self
    {
        $this->httpHandlers->add($httpHandler);

        return $this;
    }

    /**
     * @return Collection<class-string<Action>>
     */
    public function getSyncActions(): Collection
    {
        return $this->syncActions;
    }

    /**
     * @param class-string<Action>[] $syncActions
     */
    public function setSyncActions(array $syncActions): self
    {
        $this->syncActions = CollectionFactory::from($syncActions);

        return $this;
    }

    /**
     * @param class-string<Action> $action
     */
    public function addSyncAction(string $action): self
    {
        $this->syncActions->add($action);

        return $this;
    }

}