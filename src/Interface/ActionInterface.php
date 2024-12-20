<?php

namespace molibdenius\CQRS\Interface;

use Stringable;

interface ActionInterface
{
    public function getState(): string;
    public function setState(string $state): void;
    public function getType(): string;
    public function setType(string $type): void;
    public function getPayloadType(): string;
    public function setPayloadType(string $payloadType): void;

    /**
     * @param mixed[] $attributes
     */
    public function load(array $attributes): void;
}