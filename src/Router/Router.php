<?php

namespace molibdenius\CQRS\Router;

use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Action\Enum\ActionType;
use molibdenius\CQRS\Action\Enum\PayloadType;
use molibdenius\CQRS\Handler\Handler;
use Psr\Http\Message\ServerRequestInterface;
use WS\Utils\Collections\ArrayStrictList;

final class Router
{
    /**
     * @var ArrayStrictList<RouteCard>
     */
    private ArrayStrictList $routeRegistry;

    /**
     * @var ArrayStrictList<HandlerCard>
     */
    private ArrayStrictList $handlerRegistry;

    public function __construct()
    {
        $this->routeRegistry = new ArrayStrictList();
        $this->handlerRegistry = new ArrayStrictList();
    }

    /**
     * @param string $path
     * @param HttpMethod $method
     * @param PayloadType $payloadType
     * @param class-string<Action> $action
     * @param class-string<Handler> $handler
     * @param ActionType $type
     * @param string|null $name
     *
     * @return void
     */
    public function registerRoute(
        string      $path,
        HttpMethod  $method,
        PayloadType $payloadType,
        string      $action,
        string      $handler,
        ActionType  $type,
        string|null $name = null
    ): void
    {
        $this->routeRegistry->add(new RouteCard($path, $method, $payloadType, $action, $name));

        $this->handlerRegistry->add(new HandlerCard($action, $type, $handler));
    }

    /** @return class-string<Action> */
    public function resolveAction(ServerRequestInterface $request): string
    {
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();

        return $this->routeRegistry->stream()
            ->findFirst(function (RouteCard $route) use ($path, $method) {
                return $route->path === $path && $route->method === HttpMethod::get($method);
            })->action;
    }

    /**
     * @param class-string<Action> $action
     *
     * @return class-string<Handler>
     */
    public function resolveHandler(string $action): string
    {
        return $this->handlerRegistry->stream()
            ->findFirst(function (HandlerCard $handler) use ($action) {
                return $handler->action === $action;
            })->handler;
    }

    /**
     * @param class-string<Action> $action
     *
     * @return ActionType
     */
    public function getActionType(string $action): ActionType
    {
        return $this->handlerRegistry->stream()
            ->findFirst(function (HandlerCard $handler) use ($action) {
                return $handler->action === $action;
            })->type;
    }

    public function getActionPayloadType(string $action): PayloadType
    {
        return $this->routeRegistry->stream()
            ->findFirst(function (RouteCard $route) use ($action) {
                return $route->action === $action;
            })->payloadType;
    }

}