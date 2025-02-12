<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;


use molibdenius\CQRS\Component;
use Spiral\RoadRunner\Jobs\Jobs;

return [
    Component::Jobs->value,
    static function (ServicesConfigurator $services) {
        $services
            ->set(Jobs::class)
            ->arg('$rpc', service(Component::RPC->value));

        return $services->alias(Component::Jobs->value, Jobs::class)->public();
    }
];