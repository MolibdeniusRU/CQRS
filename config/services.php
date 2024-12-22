<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use molibdenius\CQRS\Enum\Service;
use Nyholm\Psr7\Factory\Psr17Factory;
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner\Environment;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Jobs\Jobs;
use Spiral\RoadRunner\Worker;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $setUp = [
        Service::Environment->value => static function (ServicesConfigurator $services) {
            return $services->set(Service::Environment->value, Environment::class)
                ->constructor('fromGlobals');
        },
        Service::RPC->value => static function (ServicesConfigurator $services) {
            return $services->set(Service::RPC->value, RPC::class)
                ->constructor('create')
                ->arg('$connection', expr("service(Service::Environment->value).getRPCAddress()"));
        },
        Service::Jobs->value => static function (ServicesConfigurator $services) {
            return $services->set(Service::Jobs->value, Jobs::class)
                ->constructor(service(Service::RPC->value));
        },
        Service::PSR17Factory->value => static function (ServicesConfigurator $services) {
            return $services->set(Service::PSR17Factory->value, Psr17Factory::class);
        },
        Service::RRWorker->value => static function (ServicesConfigurator $services) {
            return $services->set(Service::RRWorker->value, Worker::class
            )->constructor('create');
        },
        Service::PSR7Worker->value => static function (ServicesConfigurator $services) {
            return $services->set(Service::PSR7Worker->value, PSR7Worker::class)
                ->args([
                    service(Service::RRWorker->value),
                    service(Service::PSR17Factory->value),
                    service(Service::PSR17Factory->value),
                    service(Service::PSR17Factory->value),
                ]);
        }
    ];
};