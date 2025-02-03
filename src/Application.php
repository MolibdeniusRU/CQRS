<?php

namespace molibdenius\CQRS;

require_once __DIR__ . '/../helpers/functions.php';

use Exception;
use molibdenius\CQRS\Bus\ActionBus;
use molibdenius\CQRS\Dispatcher\Dispatcher;
use molibdenius\CQRS\Dispatcher\HttpDispatcher;
use molibdenius\CQRS\Dispatcher\QueueDispatcher;
use molibdenius\CQRS\Handler\Handler;
use ReflectionClass;
use Spiral\RoadRunner\Environment;
use Spiral\RoadRunner\Http\PSR7WorkerInterface;
use Spiral\RoadRunner\Jobs\ConsumerInterface;
use Spiral\RoadRunner\Jobs\JobsInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Throwable;
use WS\Utils\Collections\ArrayList;
use WS\Utils\Collections\ArrayStrictList;


final class Application
{
    /** @var ArrayList<Dispatcher> */
    private ArrayList $dispatchers;

    private bool $isInitialized = false;

    private ApplicationMode $applicationMode;

    private Environment $env;

    /**
     * @param Environment|null $env
     * @param ApplicationMode|null $applicationMode
     */
    public function __construct(?Environment $env = null, ?ApplicationMode $applicationMode = null)
    {
        if ($env === null) {
            $env = Environment::fromGlobals();
        }

        $this->env = $env;

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

            /** @var ActionBus $bus */
            $bus = $container->get(Service::ActionBus->value);

            $definitions = new ArrayStrictList();
            $definitions->addAll($container->getDefinitions());
            $definitions->stream()
                ->map(function (Definition $definition) use ($bus) {
                    if ($definition->hasTag('cqrs.handler')) {
                        /** @var class-string<Handler> $handlerClass */
                        $handlerClass = $definition->getClass();

                        $bus->registerHandler(new ReflectionClass($handlerClass));
                    }
                });

            /** @var PSR7WorkerInterface $PSR7Worker */
            $PSR7Worker = $container->get(Service::PSR7Worker->value);

            /** @var JobsInterface $jobs */
            $jobs = $container->get(Service::Jobs->value);

            /** @var ConsumerInterface $consumer */
            $consumer = $container->get(Service::Consumer->value);

            $this->dispatchers->addAll([
                new HttpDispatcher($PSR7Worker, $jobs, $bus),
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
                if ($dispatcher->canServe($this->env)) {
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
        $phpLoader->load(__DIR__ . '/../config/services.php');

        $yamlLoader = new YamlFileLoader($container, $fileLocator);
        $yamlLoader->load(get_project_dir() . '/config/services.yaml');

        $container->registerForAutoconfiguration(Handler::class)->addTag('cqrs.handler');
        $container->compile();

        return $container;
    }

}

