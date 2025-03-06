<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use molibdenius\CQRS\Component;
use Spiral\Goridge\RPC\RPC;
use Spiral\Goridge\RPC\RPCInterface;


return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(Component::RPC->value, RPC::class)
        ->factory([RPC::class, 'create'])
        ->arg('$connection', expr("service('" . Component::Environment->value . "').getRPCAddress()"))
        ->alias(RPC::class, Component::RPC->value)->public()
        ->alias(RPCInterface::class, Component::RPC->value);
};