<?php

namespace molibdenius\CQRS\Dispatcher;

use Spiral\RoadRunner\EnvironmentInterface;

interface Dispatcher
{
    public function canServe(EnvironmentInterface $env): bool;

    public function serve(): void;
}