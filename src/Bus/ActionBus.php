<?php

namespace molibdenius\CQRS\Bus;

use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Action\Enum\ActionState;
use molibdenius\CQRS\Handler\Attribute\ActionHandler;
use molibdenius\CQRS\Handler\Handler;
use molibdenius\CQRS\Router\Router;
use Psr\Container\ContainerInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\Definition;
use Throwable;
use WS\Utils\Collections\ArrayList;
use WS\Utils\Collections\Collection;
use WS\Utils\Collections\Functions\Reorganizers;
use WS\Utils\Collections\HashMap;
use WS\Utils\Collections\Map;

final readonly class ActionBus implements Bus
{
    private Map $handlersMap;

    private Map $metadataMap;

    public function __construct(
        #[AutowireLocator('cqrs.handler')]
        private ContainerInterface $handlers,
    )
    {
        $this->handlersMap = new HashMap();
        $this->metadataMap = new HashMap();
    }


    /**
     * @param Collection<Definition> $definitions
     * @param Router $router
     * @throws ReflectionException
     */
    public function registerHandlers(Collection $definitions, Router $router): void
    {
        $handlers = [];

        $definitions->stream()
            ->reorganize(Reorganizers::collapse())
            ->map(
                function (Definition $definition) use (&$handlers) {
                    if ($definition->hasTag('cqrs.handler')) {
                        $handlers[] = $definition->getClass();
                    }
                }
            );

        foreach ($handlers as $handlerClass) {
            /** @var class-string<Handler<Action>> $handlerClass */
            $attributes = ArrayList::of((new ReflectionClass($handlerClass))->getAttributes());

            $attributes->stream()
                ->reorganize(Reorganizers::collapse())
                ->map(
                    function (ReflectionAttribute $reflectionAttribute) use ($handlerClass) {
                        $attribute = $reflectionAttribute->newInstance();
                        if ($attribute instanceof ActionHandler) {
                            $this->handlersMap->put($attribute->actionClass, $handlerClass);
                            $this->metadataMap->put($attribute->actionClass, $attribute);
                        }
                    }
                );
        }

        $router->setResource($handlers);
    }

    public function dispatch(Action $action): mixed
    {
        try {
            $handler = $this->handlers->get($this->handlersMap->get($action::class));
            if (!$handler instanceof Handler) {
                throw new RuntimeException(sprintf("Class %s does not implement " . Handler::class, $handler::class));
            }

            $result = $handler->handle($action);
        } catch (Throwable $exception) {
            file_put_contents('php://stderr', $exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
            $result = null;
        }

        return $result;
    }

    /**
     * @param class-string<Action> $actionClass
     */
    public function resolveAction(string $actionClass): Action
    {
        $action = new $actionClass();
        $action->setActionType($this->metadataMap->get($actionClass)->type);
        $action->setActionState(ActionState::New);
        $action->setActionPayloadTypes($this->metadataMap->get($actionClass)->payloadTypes);

        return $action;
    }
}