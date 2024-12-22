<?php

namespace molibdenius\CQRS;

use molibdenius\CQRS\Attribute\ActionHandler;
use molibdenius\CQRS\Dispatcher\HttpDispatcher;
use molibdenius\CQRS\Dispatcher\QueueDispatcher;
use molibdenius\CQRS\Interface\DispatcherInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use Spiral\RoadRunner\Environment;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Throwable;

class Application
{
    /** @var DispatcherInterface[] */
    private array $dispatchers;

    private Environment $env;

    private ?ContainerBuilder $container = null;

    private string $projectDir;

    public function __construct()
    {
        $class = get_called_class();
        try {
            $container = $this->getContainer();
            $loader = new YamlFileLoader($container, new FileLocator($this->getConfigDir()));
            $loader->load('services.yaml');
            $container->

            $this->env = Environment::fromGlobals();

            $bus = $container->get(ActionBus::class);
            $router = $container->get(Router::class);

            foreach ($container->getParameter('handlers_dirs') as $dir) {
                $this->registerHandlers($dir, ActionHandler::class, $bus, $router);
            }

            $httpDispatcher = $container->get(HttpDispatcher::class);
            $httpDispatcher->setRouter($router);
            $httpDispatcher->setBus($bus);

            $queueDispatcher = new QueueDispatcher();
            $queueDispatcher->setBus($bus);

            $this->dispatchers = [
                $httpDispatcher,
                $queueDispatcher,
            ];


        } catch (Throwable $exception) {
            file_put_contents('php://stderr', $exception->getMessage());
        }

    }

    /**
     * @throws ReflectionException
     */
    public function registerHandlers(string $dir, string $attributeClass, ActionBus $bus, Router $router): void
    {
        foreach (glob($dir. '/*.php', GLOB_NOSORT) as $handlerFilePath) {
            preg_match('/src\/(?P<className>[A-Za-z\/]+).php/', $handlerFilePath, $matches);
            $handlerClass = __NAMESPACE__ . '\\' .  str_replace('/', '\\', $matches['className']);

            $attributes = (new ReflectionClass($handlerClass))->getAttributes();
            if (count($attributes) > 0) {
                array_map(
                    static function (ReflectionAttribute $attributeName) use ($handlerClass, $router) {
                        /** @var ActionHandler $handlerAttribute */
                        $handlerAttribute = $attributeName->newInstance();

                        $router->registerRoute($handlerAttribute->path, $handlerAttribute->method, $handlerClass);
                    },
                    $attributes,
                );
            }
        }
    }

    public function run(): void
    {
        foreach ($this->dispatchers as $dispatcher) {
            if ($dispatcher->canServe($this->env)) {
                $dispatcher->serve();
            }
        }
    }

    public function getContainer(): ContainerBuilder
    {
        if ($this->container === null) {
            $this->container = new ContainerBuilder();
        }

        return $this->container;
    }

    public function setContainer(ContainerBuilder $container): void
    {
        $this->container = $container;
    }

    public function getProjectDir(): string
    {
        if (!isset($this->projectDir)) {
            $r = new \ReflectionObject($this);

            if (!is_file($dir = $r->getFileName())) {
                throw new \LogicException(\sprintf('Cannot auto-detect project dir for Application of class "%s".', $r->name));
            }

            $dir = $rootDir = \dirname($dir);
            while (!is_file($dir.'/composer.json')) {
                if ($dir === \dirname($dir)) {
                    return $this->projectDir = $rootDir;
                }
                $dir = \dirname($dir);
            }
            $this->projectDir = $dir;
        }

        return $this->projectDir;
    }

    public function getConfigDir(): string
    {
        return $this->getProjectDir() . '/config';
    }

}

