<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;


use molibdenius\CQRS\Component;
use molibdenius\CQRS\Dispatcher\QueueDispatcher;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(Component::QueueDispatcher->value, QueueDispatcher::class)
        ->autowire()
        ->autoconfigure()
        ->alias(QueueDispatcher::class, Component::QueueDispatcher->value)->public();
};