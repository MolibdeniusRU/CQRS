<?php

namespace molibdenius\CQRS;

require_once __DIR__ . '/../helpers/functions.php';

use Exception;
use molibdenius\CQRS\Dispatcher\Dispatcher;
use molibdenius\CQRS\Dispatcher\HttpDispatcher;
use molibdenius\CQRS\Dispatcher\QueueDispatcher;
use ReflectionClass;
use Spiral\RoadRunner\Environment;
use Spiral\RoadRunner\Http\PSR7WorkerInterface;
use Spiral\RoadRunner\Jobs\ConsumerInterface;
use Spiral\RoadRunner\Jobs\JobsInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Throwable;
use WS\Utils\Collections\ArrayList;
use WS\Utils\Collections\Collection;


final class Application
{
    /** @var ArrayList<Dispatcher> */
    private ArrayList $dispatchers;

    private string $projectDir;

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
            $container->compile();

            /** @var ActionBus $bus */
            $bus = $container->get(Service::ActionBus->value);

            foreach ($container->getDefinitions() as $name => $definition) {
                if ($this->isHandler(Arraylist::of($container->getParameter('handlers_namespaces')), $name)) {
                    /** @var class-string $handlerClass */
                    $handlerClass = $definition->getClass();

                    $bus->registerHandler(new ReflectionClass($handlerClass));
                }
            }

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
        return ContainerFactory::create($this->getAppConfigDir(), $this->getProjectConfigDir());
    }

    private function getApplicationDir(): string
    {
        return dirname(__DIR__);
    }

    public function getProjectDir(): string
    {
        if (!isset($this->projectDir)) {
            $this->projectDir = get_project_dir();
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
     * @param Collection $namespaces
     * @param string $name
     * @return bool
     */
    private function isHandler(Collection $namespaces, string $name): bool
    {
        $isContain = $namespaces->stream()->findFirst(function (string $namespace) use ($name): bool {
            return str_contains($name, $namespace);
        });

        return $isContain !== null;
    }
}

