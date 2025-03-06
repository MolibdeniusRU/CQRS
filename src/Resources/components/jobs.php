<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;


use molibdenius\CQRS\Component;
use Spiral\RoadRunner\Jobs\Jobs;
use Spiral\RoadRunner\Jobs\JobsInterface;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(Component::Jobs->value, Jobs::class)
        ->arg('$rpc', service(Component::RPC->value))
        ->alias(Jobs::class, Component::Jobs->value)->public()
        ->alias(JobsInterface::class, Component::Jobs->value);
};