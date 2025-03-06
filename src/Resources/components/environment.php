<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use molibdenius\CQRS\Component;
use Spiral\RoadRunner\Environment;
use Spiral\RoadRunner\EnvironmentInterface;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(Component::Environment->value, Environment::class)->public()
        ->factory([Environment::class, 'fromGlobals'])
        ->alias(Environment::class, Component::Environment->value)->public()
        ->alias(EnvironmentInterface::class, Component::Environment->value)->public();
};