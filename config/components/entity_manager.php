<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use molibdenius\CQRS\ApplicationMode;
use molibdenius\CQRS\Component;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

return [
    Component::EntityManager->value,
    static function (ServicesConfigurator $services) {
        $databaseUrl = $_ENV['DATABASE_URL'];
        $applicationMode = $_ENV['APPLICATION_MODE'];

        $dnsParser = new DsnParser(require __DIR__ . '/../pdo_map.php');

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
            ->set(Component::EmConfiguration->value, Configuration::class)
            ->factory([ORMSetup::class, 'createAttributeMetadataConfiguration'])
            ->arg('$paths', [get_project_dir() . 'src/Entity'])
            ->arg('$isDevMode', $applicationMode === ApplicationMode::Development->value)
            ->arg('$proxyDir', get_project_dir() . '/runtime/orm_proxies')
            ->call('setMetadataCache', [service($metadataCache)])
            ->call('setQueryCache', [service($queryCache)])
            ->call('setAutoGenerateProxyClasses', [$applicationMode === ApplicationMode::Development->value]);

        $services
            ->set(Component::EmConnection->value, Connection::class)
            ->factory([DriverManager::class, 'getConnection'])
            ->arg('$params', $dnsParser->parse($databaseUrl))
            ->arg('$config', service(Component::EmConfiguration->value));

        $services
            ->set(EntityManager::class)
            ->args([service(Component::EmConnection->value), service(Component::EmConfiguration->value)]);

        return $services->alias(Component::EntityManager->value, EntityManager::class)->public();
    }
];