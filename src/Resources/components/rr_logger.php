<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use molibdenius\CQRS\Component;
use RoadRunner\Logger\Logger;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(Component::RRLogger->value, Logger::class)
        ->args([service(Component::RPC->value)])
        ->alias(Logger::class, Component::RRLogger->value)->public();
};