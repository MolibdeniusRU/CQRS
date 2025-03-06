<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use molibdenius\CQRS\Component;
use molibdenius\CQRS\Dispatcher\HttpDispatcher;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(Component::HttpDispatcher->value, HttpDispatcher::class)
        ->autowire()
        ->autoconfigure()
        ->alias(HttpDispatcher::class, Component::HttpDispatcher->value)->public();
};
