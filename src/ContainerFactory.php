<?php

namespace molibdenius\CQRS;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ContainerFactory
{
    /**
     * @throws Exception
     */
    public static function create(string $appConfigDir, string $projectConfigDir): ContainerBuilder
    {
        $container = new ContainerBuilder();

        $phpLoader = new PhpFileLoader($container, new FileLocator(__DIR__));
        $phpLoader->load($appConfigDir . '/services.php');

        $yamlLoader = new YamlFileLoader($container, new FileLocator(__DIR__));
        $yamlLoader->load($projectConfigDir . '/services.yaml');

        return $container;
    }
}