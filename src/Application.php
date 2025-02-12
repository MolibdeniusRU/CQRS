<?php

namespace molibdenius\CQRS;

require_once __DIR__ . '/../helpers/functions.php';

use Exception;
use molibdenius\CQRS\Bus\ActionBus;
use molibdenius\CQRS\Dispatcher\Dispatcher;
use molibdenius\CQRS\Dispatcher\HttpDispatcher;
use molibdenius\CQRS\Dispatcher\QueueDispatcher;
use molibdenius\CQRS\Handler\Handler;
use molibdenius\CQRS\Router\Router;
use Spiral\RoadRunner\Environment;
use Spiral\RoadRunner\Http\PSR7WorkerInterface;
use Spiral\RoadRunner\Jobs\ConsumerInterface;
use Spiral\RoadRunner\Jobs\JobsInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Routing\DependencyInjection\RoutingResolverPass;
use Throwable;
use WS\Utils\Collections\ArrayList;


final class Application
{
    /** @var ArrayList<Dispatcher> */
    private ArrayList $dispatchers;

    private bool $isInitialized = false;

    private ApplicationMode $applicationMode;

    public function __construct(?ApplicationMode $applicationMode = null)
    {
        if ($applicationMode === null) {
            $applicationMode = ApplicationMode::Production;
        }

        $this->applicationMode = $applicationMode;

        $this->dispatchers = new ArrayList();
    }

    public function init(): void
    {
        try {
            $container = $this->initContainer();

            /** @var Router $router */
            $router = $container->get(Component::Router->value);

            /** @var ActionBus $bus */
            $bus = $container->get(Component::ActionBus->value);
            $bus->registerHandlers(ArrayList::of($container->getDefinitions()), $router);

            /** @var PSR7WorkerInterface $PSR7Worker */
            $PSR7Worker = $container->get(Component::PSR7Worker->value);

            /** @var JobsInterface $jobs */
            $jobs = $container->get(Component::Jobs->value);

            /** @var ConsumerInterface $consumer */
            $consumer = $container->get(Component::Consumer->value);

            $this->dispatchers->addAll([
                new HttpDispatcher($PSR7Worker, $jobs, $bus, $router),
                new QueueDispatcher($consumer, $bus)
            ]);

        } catch (Throwable $exception) {
            $data = $exception->getMessage();
            if ($this->applicationMode !== ApplicationMode::Production) {
                $data .= PHP_EOL . $exception->getTraceAsString();
            }

            file_put_contents('php://stderr', $data);
        }

        $this->isInitialized = true;
    }

    public function run(): void
    {
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
    }

    /**
     * @throws Exception
     */
    private function initContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();

        $fileLocator = new FileLocator(__DIR__);

        $phpLoader = new PhpFileLoader($container, $fileLocator);
        $phpLoader->load(__DIR__ . '/../config/components.php');

        $yamlLoader = new YamlFileLoader($container, $fileLocator);
        $yamlLoader->load(get_project_dir() . '/config/services.yaml');

        $container->registerForAutoconfiguration(Handler::class)->addTag('cqrs.handler');
        $container->addCompilerPass(new RoutingResolverPass());
        $container->compile();

        return $container;
    }

}

