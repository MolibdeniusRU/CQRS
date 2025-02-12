<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use molibdenius\CQRS\Component;
use Spiral\RoadRunner\Http\PSR7Worker;

return [
    Component::PSR7Worker->value,
    static function (ServicesConfigurator $services) {
        $services
            ->set(PSR7Worker::class)
            ->args([
                service(Component::RRWorker->value),
                service(Component::PSR17Factory->value),
                service(Component::PSR17Factory->value),
                service(Component::PSR17Factory->value),
            ]);

        return $services->alias(Component::PSR7Worker->value, PSR7Worker::class)->public();
    }
];