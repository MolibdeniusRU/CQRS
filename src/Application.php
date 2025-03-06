<?php

namespace molibdenius\CQRS;

require_once __DIR__ . '/../helpers/functions.php';

use Exception;
use molibdenius\CQRS\Bus\ActionBus;
use molibdenius\CQRS\DependencyInjection\CQRSExtension;
use molibdenius\CQRS\Dispatcher\Dispatcher;
use molibdenius\CQRS\Dispatcher\HttpDispatcher;
use molibdenius\CQRS\Dispatcher\QueueDispatcher;
use molibdenius\CQRS\Handler\Handler;
use ReflectionException;
use RoadRunner\Logger\Logger;
use Spiral\RoadRunner\Environment;
use Symfony\Component\Cache\DependencyInjection\CachePoolClearerPass;
use Symfony\Component\Cache\DependencyInjection\CachePoolPass;
use Symfony\Component\Cache\DependencyInjection\CachePoolPrunerPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Routing\DependencyInjection\RoutingResolverPass;
use Symfony\Component\Serializer\DependencyInjection\SerializerPass;
use Throwable;
use WS\Utils\Collections\Collection;
use WS\Utils\Collections\CollectionFactory;


final class Application
{
    /** @var Collection<Dispatcher> */
    private Collection $dispatchers;

    private Logger $logger;

    private bool $isInitialized = false;

    private ApplicationMode $applicationMode;

    public function __construct(?ApplicationMode $applicationMode = null)
    {
        if ($applicationMode === null) {
            $applicationMode = ApplicationMode::Development;
        }

        $this->applicationMode = $applicationMode;

        $this->dispatchers = CollectionFactory::empty();
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function init(): void
    {
        $container = $this->initContainer();

        /** @var ActionBus $bus */
        $bus = $container->get(ActionBus::class);

        /** @var class-string<Handler>[] $handlers */
        $handlers = array_keys($container->findTaggedServiceIds('cqrs.handler'));

        $bus->registerHandlers($handlers);

        $this->dispatchers->addAll([
            $container->get(HttpDispatcher::class),
            $container->get(QueueDispatcher::class),
        ]);

        if (!($logger = $container->get(Logger::class)) instanceof Logger) {
            throw new \RuntimeException('Logger service not found');
        }

        $this->logger = $logger;

        $this->isInitialized = true;
    }

    public function run(): void
    {
        try {
            if (!$this->isInitialized) {
                $this->init();
            }

            $this->dispatchers
                ->stream()
                ->map(function (Dispatcher $dispatcher) {
                    if ($dispatcher->canServe(Environment::fromGlobals())) {
                        $dispatcher->serve();
                    }
                });

        } catch (Throwable $exception) {
            $data = $exception->getMessage();

            if ($this->applicationMode !== ApplicationMode::Development) {
                $data .= PHP_EOL . $exception->getTraceAsString();
            }

            if ($this->isInitialized) {
                $this->logger->error($data);
            } else {
                file_put_contents('php://stderr', $data);
            }
        }
    }

    /**
     * @throws Exception
     */
    private function initContainer(): ContainerBuilder
    {
        $extension = new CQRSExtension();
        $container = new ContainerBuilder();

        $container->registerExtension($extension);
        $container
            ->loadFromExtension($extension->getAlias())
            ->addCompilerPass(new RoutingResolverPass())

        $yamlLoader = new YamlFileLoader($container, new FileLocator(get_project_dir()));
        $yamlLoader->load('./config/services.yaml');

        $container->compile();

        return $container;
    }

}

