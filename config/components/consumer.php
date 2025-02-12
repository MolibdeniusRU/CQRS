<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use molibdenius\CQRS\Component;
use Spiral\RoadRunner\Jobs\Consumer;

return [
    Component::Consumer->value,
    static function (ServicesConfigurator $services) {
        $services->set(Consumer::class);

        return $services->alias(Component::Consumer->value, Consumer::class)->public();
    }
];