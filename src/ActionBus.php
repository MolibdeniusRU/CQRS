<?php

namespace molibdenius\CQRS;

use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Action\Enum\ActionState;
use molibdenius\CQRS\Handler\Attribute\ActionHandler;
use molibdenius\CQRS\Handler\Handler;
use molibdenius\CQRS\Router\Router;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use WS\Utils\Collections\ArrayList;
use WS\Utils\Collections\Functions\Reorganizers;

final readonly class ActionBus
{
    public function __construct(
        private ContainerInterface $container,
        private Router             $router,
    )
    {
    }

    /**
     * @param ReflectionClass<Handler> $handlerReflection
     *
     * @return void
     */
    public function registerHandler(ReflectionClass $handlerReflection): void
    {
        $attributes = ArrayList::of($handlerReflection->getAttributes());
        $attributes->stream()
            ->reorganize(Reorganizers::collapse())
            ->filter(fn(ReflectionAttribute $attribute) => $attribute->newInstance() instanceof ActionHandler)
            ->map(function (ReflectionAttribute $attribute) use ($handlerReflection) {
                /** @var ActionHandler $actionAttribute */
                $actionAttribute = $attribute->newInstance();
                $this->router->registerRoute(
                    $actionAttribute->path,
                    $actionAttribute->method,
                    $actionAttribute->payloadType,
                    $actionAttribute->actionClass,
                    $handlerReflection->getName(),
                    $actionAttribute->type,
                );
            });
    }

    public function dispatch(Action $action): mixed
    {
        $actionClass = get_class($action);
        $handler = $this->container->get($this->router->resolveHandler($actionClass));
        if (!$handler instanceof Handler) {
            throw new RuntimeException(sprintf("Class %s does not implement HandlerInterface", $handler::class));
        }

        return $handler->handle($action);
    }

    public function resolveAction(ServerRequestInterface $request): Action
    {
        $actionClass = $this->router->resolveAction($request);
        /** @var Action $action */
        $action = new $actionClass();
        $action->setType($this->router->getActionType($actionClass));
        $action->setState(ActionState::New);

        return $action;
    }
}