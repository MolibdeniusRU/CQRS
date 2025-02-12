<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use molibdenius\CQRS\Component;
use Spiral\Goridge\RPC\RPC;


return [
    Component::RPC->value,
    static function (ServicesConfigurator $services) {
        $services
            ->set(RPC::class)
            ->factory([RPC::class, 'create'])
            ->arg('$connection', expr("service('" . Component::Environment->value . "').getRPCAddress()"));

        return $services->alias(Component::RPC->value, RPC::class)->public();
    }
];