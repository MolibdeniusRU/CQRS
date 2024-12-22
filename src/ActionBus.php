<?php

namespace molibdenius\CQRS;

use molibdenius\CQRS\Attribute\ActionHandler;
use molibdenius\CQRS\Enum\ActionState;
use molibdenius\CQRS\Enum\ActionType;
use molibdenius\CQRS\Interface\ActionInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\DependencyInjection\Container;
use WS\Utils\Collections\ArrayList;

class ActionBus
{
    private Container $container;

    private Router $router;

    public function registerHandler(ReflectionClass $handlerReflection): void
    {
        $attributes = ArrayList::of($handlerReflection->getAttributes());
        $attributes->stream()
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
                    ActionType::get($actionAttribute->type)
                );
            });
    }

    public function dispatch(ActionInterface $action): mixed
    {
        $actionClass = get_class($action);
        if (null === $handler = $this->container->get($this->router->resolveHandler($actionClass))) {
            throw new RuntimeException('Not found');
        }

        return $handler->handle($action);
    }

    public function resolveAction(ServerRequestInterface $request): ActionInterface
    {
        $actionClass = $this->router->resolveAction($request);
        /** @var ActionInterface $action */
        $action = new $actionClass();
        $action->setType($this->router->getActionType($actionClass));
        $action->setState(ActionState::New->value);

        return $action;
    }
}