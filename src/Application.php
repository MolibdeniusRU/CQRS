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
use Symfony\Component\Serializer\{
    Encoder\JsonEncoder,
    Encoder\YamlEncoder,
    Mapping\Factory\ClassMetadataFactory,
    Mapping\Loader\AttributeLoader,
    Normalizer\ArrayDenormalizer,
    Normalizer\DenormalizerInterface,
    Normalizer\GetSetMethodNormalizer,
    Normalizer\JsonSerializableNormalizer,
    Normalizer\NormalizableInterface,
    Normalizer\ObjectNormalizer,
    Normalizer\PropertyNormalizer,
    Serializer,
    SerializerInterface
};
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;
use Throwable;

class Application
{
    /** @var DispatcherInterface[] */
    private array $dispatchers;

    private Environment $env;

    private SerializerInterface|NormalizableInterface|DenormalizerInterface|null $serializer;

    private ?ContainerInterface $container = null;

    public function __construct(?SerializerInterface $serializer = null)
    {
        $this->serializer = $serializer;

        if ($this->serializer === null) {
            $this->serializer = $this->getSerializer();
        }

        try {
            $config = $this->serializer->denormalize(
                Yaml::parseFile(__DIR__ . '/../config.yaml'),
                Config::class,
            );

            $this->env = Environment::fromGlobals();


            $bus = new ActionBus();
            $router = new Router();

            foreach ($config->handlers_dirs as $dir) {
                $this->registerHandlers($dir, ActionHandler::class, $bus, $router);
            }

            $httpDispatcher = new HttpDispatcher();
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
                    static function (ReflectionAttribute $attributeName) use ($bus, $handlerClass, $router) {
                        /** @var ActionHandler $handlerAttribute */
                        $handlerAttribute = $attributeName->newInstance();

                        $bus->registerHandler($handlerAttribute->actionClass, new $handlerClass());

                        $router->registerRoute($handlerAttribute->path, $handlerAttribute->method, $handlerClass);
                    },
                    $attributes,
                );
            }
        }
    }

    public function getSerializer(): SerializerInterface|NormalizableInterface|DenormalizerInterface
    {
        if (!isset($this->serializer)) {
            $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());
            $normalizers = [
                new ObjectNormalizer($classMetadataFactory),
                new ArrayDenormalizer(),
                new JsonSerializableNormalizer($classMetadataFactory),
                new GetSetMethodNormalizer($classMetadataFactory),
                new PropertyNormalizer($classMetadataFactory)
            ];
            $encoders = [new YamlEncoder(), new JsonEncoder()];
            $this->serializer = new Serializer($normalizers, $encoders);
        }

        return $this->serializer;
    }

    public function run(): void
    {
        foreach ($this->dispatchers as $dispatcher) {
            if ($dispatcher->canServe($this->env)) {
                $dispatcher->serve();
            }
        }
    }

    public function getContainer(): ContainerInterface
    {
        if ($this->container === null) {
            $this->container = new ContainerBuilder();
        }

        return $this->container;
    }

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

}

