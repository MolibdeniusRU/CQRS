<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use molibdenius\CQRS\Component;
use Spiral\RoadRunner\Environment;

return [
    Component::Environment->value,
    static function (ServicesConfigurator $services) {
        $services
            ->set(Environment::class)
            ->factory([Environment::class, 'fromGlobals']);

        return $services->alias(Component::Environment->value, Environment::class)->public();
    }
];