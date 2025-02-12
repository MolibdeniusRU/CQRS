<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use molibdenius\CQRS\Component;
use Nyholm\Psr7\Factory\Psr17Factory;

return [
    Component::PSR17Factory->value,
    static function (ServicesConfigurator $services) {
        $services->set(Psr17Factory::class);

        return $services->alias(Component::PSR17Factory->value, Psr17Factory::class)->public();
    }
];