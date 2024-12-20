<?php

namespace molibdenius\CQRS\Interface;

use Spiral\RoadRunner\EnvironmentInterface;

interface DispatcherInterface
{
    public function canServe(EnvironmentInterface $env): bool;

    public function serve(): void;
}