<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use molibdenius\CQRS\ActionBus;
use molibdenius\CQRS\Router\Router;
use molibdenius\CQRS\Service;
use Nyholm\Psr7\Factory\Psr17Factory;
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner\Environment;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Jobs\Consumer;
use Spiral\RoadRunner\Jobs\Jobs;
use Spiral\RoadRunner\Worker;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $setUp = [
        static function (ServicesConfigurator $services) {
            return $services
                ->set(Service::Environment->value, Environment::class)
                ->factory([Environment::class, 'fromGlobals']);
        },
        static function (ServicesConfigurator $services) {
            return $services
                ->set(Service::RPC->value, RPC::class)
                ->factory([RPC::class, 'create'])
                ->arg('$connection', expr("service('". Service::Environment->value ."').getRPCAddress()"));
        },
        static function (ServicesConfigurator $services) {
            return $services
                ->set(Service::Jobs->value, Jobs::class)
                ->arg('$rpc', service(Service::RPC->value));
        },
        static function (ServicesConfigurator $services) {
            return $services->set(Service::PSR17Factory->value, Psr17Factory::class);
        },
        static function (ServicesConfigurator $services) {
            return $services
                ->set(Service::RRWorker->value, Worker::class)
                ->factory([Worker::class, 'createFromEnvironment'])
                ->arg('$env', service(Service::Environment->value));
        },
        static function (ServicesConfigurator $services) {
            return $services
                ->set(Service::PSR7Worker->value, PSR7Worker::class)
                ->args([
                    service(Service::RRWorker->value),
                    service(Service::PSR17Factory->value),
                    service(Service::PSR17Factory->value),
                    service(Service::PSR17Factory->value),
                ]);
        },
        static function (ServicesConfigurator $services) {
            return $services->set(Service::Consumer->value, Consumer::class);
        },
        static function (ServicesConfigurator $services) {
            return $services
                ->set(Service::ActionBus->value, ActionBus::class)
                ->args([
                    service(Service::ServiceContainer->value),
                    service(Service::Router->value)
                ]);
        },
        static function (ServicesConfigurator $services) {
            return $services->set(Service::Router->value, Router::class);
        },
    ];

    foreach ($setUp as $name => $closure) {
        $closure($services);
    }
};