<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use molibdenius\CQRS\Bus\ActionBus;
use molibdenius\CQRS\Component;

return [
    Component::ActionBus->value,
    static function (ServicesConfigurator $services) {
        $services
            ->set(ActionBus::class)
            ->autowire()
            ->autoconfigure();

        return $services->alias(Component::ActionBus->value, ActionBus::class)->public();
    }
];