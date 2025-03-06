<?php

namespace molibdenius\CQRS\DependencyInjection;

use Exception;
use molibdenius\CQRS\Component;
use molibdenius\CQRS\Handler\Handler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
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
            Component::EntityManager,
            Component::ActionBus,
            Component::QueueDispatcher,
            Component::HttpDispatcher,
        ]);

        $components->stream()
            ->map(static function (Component $component) use ($loader) {
                $loader->load($component->value . '.php');
            });

        $container->registerForAutoconfiguration(Handler::class)->addTag('cqrs.handler');
    }

    public function getAlias(): string
    {
        return 'cqrs_extension';
    }

}