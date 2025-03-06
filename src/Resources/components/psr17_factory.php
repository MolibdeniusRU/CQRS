<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use molibdenius\CQRS\Component;
use Nyholm\Psr7\Factory\Psr17Factory;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(Component::PSR17Factory->value, Psr17Factory::class)
        ->alias(Psr17Factory::class, Component::PSR17Factory->value)->public();
};