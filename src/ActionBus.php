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
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Throwable;
use WS\Utils\Collections\ArrayList;
use WS\Utils\Collections\Functions\Reorganizers;

final readonly class ActionBus implements ServiceSubscriberInterface
{
    public function __construct(
        private ContainerInterface $container,
        private Router             $router,
    )
    {
    }

    /**
     * @param ReflectionClass<object> $handlerReflection
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
        try {
            $handler = $this->container->get($this->router->resolveHandler($action::class));
            if (!$handler instanceof Handler) {
                throw new RuntimeException(sprintf("Class %s does not implement HandlerInterface", $handler::class));
            }

            $result = $handler->handle($action);
        } catch (Throwable $exception) {
            file_put_contents('php://stderr', $exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
            $result = null;
        }

        return $result;
    }

    public function resolveAction(ServerRequestInterface $request): Action
    {
        $actionClass = $this->router->resolveAction($request);
        /** @var Action $action */
        $action = new $actionClass();
        $action->setActionType($this->router->getActionType($actionClass));
        $action->setActionState(ActionState::New);
        $action->setActionPayloadType($this->router->getActionPayloadType($actionClass));

        return $action;
    }

    public static function getSubscribedServices(): array
    {
        return [

        ];
    }
}