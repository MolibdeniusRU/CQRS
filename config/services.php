<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use molibdenius\CQRS\ActionBus;
use molibdenius\CQRS\ApplicationMode;
use molibdenius\CQRS\Router\Router;
use molibdenius\CQRS\Service;
use Nyholm\Psr7\Factory\Psr17Factory;
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner\Environment;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Jobs\Consumer;
use Spiral\RoadRunner\Jobs\Jobs;
use Spiral\RoadRunner\Worker;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . '/../helpers/functions.php';

const DEFAULT_SCHEME_MAP = [
    'db2'        => 'ibm_db2',
    'mssql'      => 'pdo_sqlsrv',
    'mysql'      => 'pdo_mysql',
    'mysql2'     => 'pdo_mysql', // Amazon RDS, for some weird reason
    'postgres'   => 'pdo_pgsql',
    'postgresql' => 'pdo_pgsql',
    'pgsql'      => 'pdo_pgsql',
    'sqlite'     => 'pdo_sqlite',
    'sqlite3'    => 'pdo_sqlite',
];


return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $setUp = [
        static function (ServicesConfigurator $services) {
            return $services
                ->set(Service::Environment->value, Environment::class)
                ->factory([Environment::class, 'fromGlobals'])
                ->public();
        },
        static function (ServicesConfigurator $services) {
            return $services
                ->set(Service::RPC->value, RPC::class)
                ->factory([RPC::class, 'create'])
                ->arg('$connection', expr("service('". Service::Environment->value ."').getRPCAddress()"))
                ->public();
        },
        static function (ServicesConfigurator $services) {
            return $services
                ->set(Service::Jobs->value, Jobs::class)
                ->arg('$rpc', service(Service::RPC->value))
                ->public();
        },
        static function (ServicesConfigurator $services) {
            return $services->set(Service::PSR17Factory->value, Psr17Factory::class)->public();
        },
        static function (ServicesConfigurator $services) {
            return $services
                ->set(Service::RRWorker->value, Worker::class)
                ->factory([Worker::class, 'createFromEnvironment'])
                ->arg('$env', service(Service::Environment->value))
                ->public();
        },
        static function (ServicesConfigurator $services) {
            return $services
                ->set(Service::PSR7Worker->value, PSR7Worker::class)
                ->args([
                    service(Service::RRWorker->value),
                    service(Service::PSR17Factory->value),
                    service(Service::PSR17Factory->value),
                    service(Service::PSR17Factory->value),
                ])
                ->public();
        },
        static function (ServicesConfigurator $services) {
            return $services->set(Service::Consumer->value, Consumer::class)->public();
        },
        static function (ServicesConfigurator $services) {
            return $services
                ->set(Service::ActionBus->value, ActionBus::class)
                ->args([
                    service(Service::ServiceContainer->value),
                    service(Service::Router->value)
                ])
                ->public();
        },
        static function (ServicesConfigurator $services) {
            return $services->set(Service::Router->value, Router::class);
        },

        static function (ServicesConfigurator $services) {
            $projectDir = get_project_dir();

            $dotenv = new Dotenv();
            $dotenv->loadEnv($projectDir .'/.env');

            $databaseUrl = $_ENV['DATABASE_URL'];
            $applicationMode = $_ENV['APPLICATION_MODE'];
            $dnsParser = new DsnParser(DEFAULT_SCHEME_MAP);

            if ($applicationMode === ApplicationMode::Development->value) {
                $metadataCache = $queryCache = ArrayAdapter::class;
                $services->set($metadataCache);
            } else {
                $queryCache = 'doctrine_queries';
                $services->set($queryCache, PhpFilesAdapter::class)
                    ->arg('namespace', $queryCache);
                $metadataCache = 'doctrine_metadata';
                $services->set($metadataCache, PhpFilesAdapter::class)
                    ->arg('namespace', $metadataCache);
            }

            $services
                ->set(Service::EmConfiguration->value, Configuration::class)
                ->factory([ORMSetup::class, 'createAttributeMetadataConfiguration'])
                ->arg('$paths', [$projectDir . 'src/Entity'])
                ->arg('$isDevMode', $applicationMode === ApplicationMode::Development->value)
                ->arg('$proxyDir', $projectDir . '/runtime/orm_proxies')
                ->call('setMetadataCache', [service($metadataCache)])
                ->call('setQueryCache', [service($queryCache)])
                ->call('setAutoGenerateProxyClasses', [$applicationMode === ApplicationMode::Development->value]);

            $services
                ->set(Service::EmConnection->value, Connection::class)
                ->factory([DriverManager::class, 'getConnection'])
                ->arg('$params', $dnsParser->parse($databaseUrl))
                ->arg('$config', service(Service::EmConfiguration->value));

            return $services
                ->set(Service::EntityManager->value, EntityManager::class)
                ->args([service(Service::EmConnection->value), service(Service::EmConfiguration->value)])
                ->public();

        }
    ];
    foreach ($setUp as $closure) {
        $closure($services);
    }
};