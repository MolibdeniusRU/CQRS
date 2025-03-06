<?php

namespace molibdenius\CQRS\Bus;

use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Action\ActionFactory;
use molibdenius\CQRS\Handler\Attribute\AsCommandHandler;
use molibdenius\CQRS\Handler\Attribute\AsQueryHandler;
use molibdenius\CQRS\Handler\Attribute\AsSyncHandler;
use molibdenius\CQRS\Handler\Handler;
use molibdenius\CQRS\Metadata\MetadataMap;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use WS\Utils\Collections\CollectionFactory;
use WS\Utils\Collections\Functions\Reorganizers;

final readonly class ActionBus implements Bus
{
    public function __construct(
        #[AutowireLocator('cqrs.handler')]
        private ContainerInterface $handlers,
        private MetadataMap $metadataMap,
    )
    {
    }


    /**
     * @param class-string<Handler>[] $handlers
     * @throws ReflectionException
     */
    public function registerHandlers(array $handlers): void
    {
        foreach ($handlers as $handlerClass) {
            $handlerReflection = new ReflectionClass($handlerClass);

            CollectionFactory::from($handlerReflection->getAttributes())->stream()
                ->reorganize(Reorganizers::collapse())
                ->map(
                    function (ReflectionAttribute $reflectionAttribute) use ($handlerClass) {
                        $attribute = $reflectionAttribute->newInstance();

                        if ($attribute instanceof AsCommandHandler || $attribute instanceof AsQueryHandler) {
                            $this->metadataMap
                                ->setMetadata($attribute->actionClass, $handlerClass, $attribute)
                                ->addHttpHandler($handlerClass);
                        }

                        if ($attribute instanceof AsSyncHandler) {
                            $this->metadataMap
                                ->setMetadata($attribute->actionClass, $handlerClass, $attribute)
                                ->addSyncAction($attribute->actionClass);
                        }
                    }
                );
        }
    }

    /**
     * @param class-string<Action> $actionClass
     * @param mixed[] $payloads
     */
    public function resolveAction(string $actionClass, array $payloads = []): Action
    {
        return ActionFactory::create(
            $actionClass,
            $this->metadataMap->getMetadata($actionClass)->type,
            $payloads
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function dispatch(Action $action): mixed
    {
        $handler = $this->handlers->get($this->metadataMap->getHandler($action::class));
        if (!$handler instanceof Handler) {
            throw new RuntimeException(sprintf("Class %s does not implement " . Handler::class, $handler::class));
        }

        return $handler->handle($action);
    }

    public function getMetadataMap(): MetadataMap
    {
        return $this->metadataMap;
    }

}