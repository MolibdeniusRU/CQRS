<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use molibdenius\CQRS\Component;
use Spiral\RoadRunner\Jobs\Consumer;
use Spiral\RoadRunner\Jobs\ConsumerInterface;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(Component::Consumer->value, Consumer::class)
        ->alias(Consumer::class, Component::Consumer->value)->public()
        ->alias(ConsumerInterface::class, Component::Consumer->value)->public();
};