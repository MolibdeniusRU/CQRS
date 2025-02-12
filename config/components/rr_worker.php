<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use molibdenius\CQRS\Component;
use Spiral\RoadRunner\Worker;

return [
    Component::RRWorker->value,
    static function (ServicesConfigurator $services) {
        $services
            ->set(Worker::class)
            ->factory([Worker::class, 'createFromEnvironment'])
            ->arg('$env', service(Component::Environment->value));

        return $services->alias(Component::RRWorker->value, Worker::class)->public();
    }
];