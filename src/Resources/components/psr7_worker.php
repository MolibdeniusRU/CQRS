<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use molibdenius\CQRS\Component;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Http\PSR7WorkerInterface;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(Component::PSR7Worker->value, PSR7Worker::class)
        ->args([
            service(Component::RRWorker->value),
            service(Component::PSR17Factory->value),
            service(Component::PSR17Factory->value),
            service(Component::PSR17Factory->value),
        ])
        ->alias(PSR7Worker::class, Component::PSR7Worker->value)->public()
        ->alias(PSR7WorkerInterface::class, Component::PSR7Worker->value);
};