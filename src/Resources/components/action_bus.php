<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use molibdenius\CQRS\Bus\ActionBus;
use molibdenius\CQRS\Bus\Bus;
use molibdenius\CQRS\Component;
use molibdenius\CQRS\Metadata\MetadataMap;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(Component::ActionBus->value . '.metadata_map', MetadataMap::class)
        ->alias(MetadataMap::class, Component::ActionBus->value . '.metadata_map')
        ->set(Component::ActionBus->value, ActionBus::class)
        ->autowire()
        ->autoconfigure()
        ->alias(ActionBus::class, Component::ActionBus->value)->public()
        ->alias(Bus::class, Component::ActionBus->value)->public();
};