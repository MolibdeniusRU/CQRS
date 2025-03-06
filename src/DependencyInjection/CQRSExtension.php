<?php

namespace molibdenius\CQRS\DependencyInjection;

use Exception;
use molibdenius\CQRS\Component;
use molibdenius\CQRS\Handler\Handler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use WS\Utils\Collections\CollectionFactory;

class CQRSExtension extends Extension
{
    /**
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__) . '/Resources/components'));

        $components = CollectionFactory::from([
            Component::Environment,
            Component::RPC,
            Component::RRLogger,
            Component::Jobs,
            Component::PSR17Factory,
            Component::RRWorker,
            Component::PSR7Worker,
            Component::Consumer,
            Component::Router,
            Component::Cache,
            Component::PropertyAccessor,
            Component::PropertyInfo,
            Component::Serializer,
            Component::Redis,
            Component::EntityManager,
            Component::ActionBus,
            Component::QueueDispatcher,
            Component::HttpDispatcher,
        ]);

        $this->setKernelParameters($container);

        $components->stream()
            ->map(static function (Component $component) use ($loader) {
                $loader->load($component->value . '.php');
            });

        $this->configurePropertyAccessor($container);

        $container->registerForAutoconfiguration(Handler::class)->addTag('cqrs.handler');
    }

    public function getAlias(): string
    {
        return 'cqrs_extension';
    }

    private function setKernelParameters(ContainerBuilder $container): void
    {
        $container->setParameter('kernel.project_dir', get_project_dir());
        $container->setParameter('kernel.container_class', Container::class);
        $container->setParameter('kernel.cache_dir', get_project_dir() . '/runtime/cache');
        $container->setParameter('serializer.mapping.cache.file', '%kernel.cache_dir%/serialization.php');
        $container->setParameter('kernel.debug', false);
    }

    private function configurePropertyAccessor(ContainerBuilder $container): void
    {
        $magicMethods = PropertyAccessor::DISALLOW_MAGIC_METHODS;
        $throw = PropertyAccessor::DO_NOT_THROW;

        $container
            ->getDefinition('property_accessor')
            ->replaceArgument(0, $magicMethods)
            ->replaceArgument(1, $throw);
    }
}