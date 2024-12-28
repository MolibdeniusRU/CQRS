<?php

namespace molibdenius\CQRS\Action;

use molibdenius\CQRS\Action\Enum\ActionState;
use molibdenius\CQRS\Action\Enum\ActionType;
use molibdenius\CQRS\Action\Enum\PayloadType;

interface Action
{
    public function getState(): ActionState;
    public function setState(ActionState $state): void;
    public function getType(): ActionType;
    public function setType(ActionType $type): void;
    public function getPayloadType(): PayloadType;
    public function setPayloadType(PayloadType $payloadType): void;

    /**
     * @param mixed[] $attributes
     */
    public function load(array $attributes): void;
}