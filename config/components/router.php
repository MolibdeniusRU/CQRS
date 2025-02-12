<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;


use molibdenius\CQRS\Component;
use molibdenius\CQRS\Router\AttributeRouteHandlerLoader;
use molibdenius\CQRS\Router\Router;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Loader\AttributeDirectoryLoader;
use Symfony\Component\Routing\Loader\AttributeFileLoader;
use Symfony\Component\Routing\Loader\ContainerLoader;
use Symfony\Component\Routing\Loader\DirectoryLoader;
use Symfony\Component\Routing\Loader\GlobFileLoader;
use Symfony\Component\Routing\Loader\PhpFileLoader;
use Symfony\Component\Routing\Loader\Psr4DirectoryLoader;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContextAwareInterface;

return [
    Component::Router->value,
    static function (ServicesConfigurator $services) {
        $env = $_ENV['APPLICATION_MODE'];

        return $services
            ->set('file_locator', FileLocator::class)
            ->args([
                get_project_dir()
            ])
            ->set('routing.resolver', LoaderResolver::class)
            ->set('routing.loader.yml', YamlFileLoader::class)
            ->args([
                service('file_locator'),
                $env,
            ])
            ->tag('routing.loader')
            ->set('routing.loader.php', PhpFileLoader::class)
            ->args([
                service('file_locator'),
                $env,
            ])
            ->tag('routing.loader')
            ->set('routing.loader.glob', GlobFileLoader::class)
            ->args([
                service('file_locator'),
                $env,
            ])
            ->tag('routing.loader')
            ->set('routing.loader.directory', DirectoryLoader::class)
            ->args([
                service('file_locator'),
                $env,
            ])
            ->tag('routing.loader')
            ->set('routing.loader.container', ContainerLoader::class)
            ->args([
                tagged_locator('routing.route_loader'),
                $env,
            ])
            ->tag('routing.loader')
            ->set('routing.loader.attribute', AttributeRouteHandlerLoader::class)
            ->args([
                $env,
            ])
            ->tag('routing.loader', ['priority' => -10])
            ->set('routing.loader.attribute.directory', AttributeDirectoryLoader::class)
            ->args([
                service('file_locator'),
                service('routing.loader.attribute'),
            ])
            ->tag('routing.loader', ['priority' => -10])
            ->set('routing.loader.attribute.file', AttributeFileLoader::class)
            ->args([
                service('file_locator'),
                service('routing.loader.attribute'),
            ])
            ->tag('routing.loader', ['priority' => -10])
            ->set('routing.loader.psr4', Psr4DirectoryLoader::class)
            ->args([
                service('file_locator'),
            ])
            ->tag('routing.loader', ['priority' => -10])
            ->set('routing.loader', DelegatingLoader::class)
            ->public()
            ->args([
                service('routing.resolver'),
                [], // Default options
                [], // Default requirements
            ])
            ->set('router.default', Router::class)
            ->args([
                service('routing.loader'),
                []
            ])
            ->alias('router', 'router.default')
            ->public()
            ->alias(Router::class, 'router')
            ->alias(UrlGeneratorInterface::class, 'router')
            ->alias(UrlMatcherInterface::class, 'router')
            ->alias(RequestContextAwareInterface::class, 'router');
    }
];