<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

require_once __DIR__ . '/../helpers/functions.php';

use molibdenius\CQRS\Component;


return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $closures = [];

    array_map(
        static function (?string $file) use (&$closures) {
            if (is_file($file)) {
                [$name, $closure] = require $file;
                $closures[$name] = $closure;
            }
        },
        glob(__DIR__ . '/components/*.php', GLOB_NOSORT) ?: []
    );

    $sort = [
        Component::Environment,
        Component::RPC,
        Component::Jobs,
        Component::PSR17Factory,
        Component::RRWorker,
        Component::PSR7Worker,
        Component::Consumer,
        Component::Router,
        Component::ActionBus,
        Component::EntityManager
    ];

    array_map(
        static function (Component $component) use ($services, $closures) {
            $closures[$component->value]($services);
        },
        $sort
    );
};