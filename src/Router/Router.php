<?php

namespace molibdenius\CQRS\Router;

use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Action\Enum\ActionType;
use molibdenius\CQRS\Action\Enum\PayloadType;
use molibdenius\CQRS\Handler\Handler;
use Psr\Http\Message\ServerRequestInterface;
use WS\Utils\Collections\ArrayStrictList;

class Router
{
    /**
     * @var ArrayStrictList<RouteRegistry>
     */
    private ArrayStrictList $routes;

    /**
     * @var ArrayStrictList<HandlerRegistry>
     */
    private ArrayStrictList $handlers;

    public function __construct()
    {
        $this->routes = new ArrayStrictList();
        $this->handlers = new ArrayStrictList();
    }

    /**
     * @param string $route
     * @param HttpMethod $method
     * @param PayloadType $payloadType
     * @param class-string $action
     * @param class-string $handler
     * @param ActionType $type
     * @param string|null $name
     *
     * @return void
     */
    public function registerRoute(
        string $route,
        HttpMethod $method,
        PayloadType $payloadType,
        string $action,
        string $handler,
        ActionType $type,
        string|null $name = null
    ): void
    {
        $this->routes->add(new RouteRegistry($route, $method, $payloadType, $action, $name));

        $this->handlers->add(new HandlerRegistry($action, $type, $handler));
    }

    /** @return class-string<Action> */
    public function resolveAction(ServerRequestInterface $request): string
    {
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();

        return $this->routes->stream()
            ->findFirst(function (RouteRegistry $route) use ($path, $method) {
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
        return $this->handlers->stream()
            ->findFirst(function (HandlerRegistry $handler) use ($action) {
               return $handler->action === $action;
            });
    }

    /**
     * @param class-string<Action> $action
     *
     * @return ActionType
     */
    public function getActionType(string $action): ActionType
    {
        return $this->handlers->stream()
            ->findFirst(function (HandlerRegistry $handler) use ($action) {
                return $handler->action === $action;
            })->type;
    }

}