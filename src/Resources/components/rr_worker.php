<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use molibdenius\CQRS\Component;
use Spiral\RoadRunner\Worker;
use Spiral\RoadRunner\WorkerInterface;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(Component::RRWorker->value, Worker::class)
        ->factory([Worker::class, 'createFromEnvironment'])
        ->arg('$env', service(Component::Environment->value))
        ->alias(Worker::class, Component::RRWorker->value)->public()
        ->alias(WorkerInterface::class, Component::RRWorker->value);
};