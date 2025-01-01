<?php

namespace molibdenius\CQRS;

use Exception;
use molibdenius\CQRS\Dispatcher\Dispatcher;
use molibdenius\CQRS\Dispatcher\HttpDispatcher;
use molibdenius\CQRS\Dispatcher\QueueDispatcher;
use ReflectionClass;
use Spiral\RoadRunner\Environment;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Throwable;
use function dirname;

final class Application
{
    /** @var Dispatcher[] */
    private array $dispatchers;

    private string $projectDir;

    private bool $isInitialized = false;

    public function init(): void
    {
        try {
            $container = $this->initContainer();
            /** @var ActionBus $bus */
            $bus = $container->get(Service::ActionBus->value);

            foreach ($container->getDefinitions() as $name => $definition) {
                if ($this->isHandler($container->getParameter('handlers_dirs'), $name)) {
                    $bus->registerHandler(new ReflectionClass($definition->getClass()));
                }
            }

            $httpDispatcher = new HttpDispatcher(
                $container->get(Service::PSR7Worker->value),
                $container->get(Service::Jobs->value),
                $bus
            );

            $queueDispatcher = new QueueDispatcher(
                $container->get(Service::Consumer->value),
                $bus
            );

            $this->dispatchers = [
                $httpDispatcher,
                $queueDispatcher,
            ];
        } catch (Throwable $exception) {
            file_put_contents('php://stderr', $exception->getMessage());
        }

        $this->isInitialized = true;
    }

    public function run(): void
    {
        if (!$this->isInitialized) {
            $this->init();
        }

        foreach ($this->dispatchers as $dispatcher) {
            if ($dispatcher->canServe(Environment::fromGlobals())) {
                $dispatcher->serve();
            }
        }
    }

    /**
     * @throws Exception
     */
    private function initContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();

        $phpLoader = new PhpFileLoader($container, new FileLocator(__DIR__));
        $phpLoader->load($this->getAppConfigDir() . '/services.php');

        $yamlLoader = new YamlFileLoader($container, new FileLocator(__DIR__));
        $yamlLoader->load($this->getProjectConfigDir() . '/services.yaml');

        return $container;
    }

    private function getApplicationDir(): string
    {
        return dirname(__DIR__);
    }

    public function getProjectDir(): string
    {
        if (!isset($this->projectDir)) {
            $dir = $rootDir = getcwd();
            while (!is_file($dir.'/composer.json')) {
                if ($dir === dirname($dir)) {
                    return $this->projectDir = $rootDir;
                }
                $dir = dirname($dir);
            }
            $this->projectDir = $dir;
        }

        return $this->projectDir;
    }

    public function getProjectConfigDir(): string
    {
        return $this->getProjectDir() . '/config';
    }

    private function getAppConfigDir(): string
    {
        return $this->getApplicationDir() . '/config';
    }

    /**
     * @param array<string> $handlersDirs
     * @param string $name
     * @return bool
     */
    private function isHandler(array $handlersDirs, string $name): bool
    {
        foreach ($handlersDirs as $dir) {
            if (str_contains($name, $dir)) {
                return true;
            }
        }
        return false;
    }
}

