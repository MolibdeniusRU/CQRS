<?php

namespace molibdenius\CQRS;

use molibdenius\CQRS\Enum\ActionType;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

/**
 *
 *
 */
class Router
{
    /**
     * @var array<string, array<string, array<string, class-string>>>
     */
    private array $routes = [];

    /**
     * @var array<class-string, array<string, class-string>>
     */
    private array $handlers = [];

    /**
     * @param string $route
     * @param string $method
     * @param string $payloadType
     * @param class-string $action
     * @param class-string $handler
     * @param ActionType $type
     * @return void
     */
    public function registerRoute(string $route, string $method, string $payloadType, string $action, string $handler, ActionType $type): void
    {
        $this->routes[$route][$method][$payloadType] = $action;
        $this->handlers[$action][$type->value] = $handler;
    }

    /** @return class-string */
    public function resolveAction(ServerRequestInterface $request): string
    {
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();

        if (!isset($this->routes[$path][$method])) {
            throw new RuntimeException('No route provided');
        }
        return array_key_first(array_reverse($this->routes[$path][$method], true));
    }

    public function resolveHandler(string $action): string
    {
        return $this->handlers[$action][$this->getActionType($action)];
    }

    public function getActionType(string $action): string
    {
        if (!isset($this->handlers[$action])) {
            throw new RuntimeException('No action provided');
        }
        return array_key_first($this->handlers[$action]);
    }

}