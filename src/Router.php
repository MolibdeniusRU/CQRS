<?php

namespace molibdenius\CQRS;

use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class Router
{
    /**
     * @var class-string[][]
     */
    private array $routes = [];

    private ServerRequestInterface $request;

    public function registerRoute(string $route, string $method, string $handler): void
    {
        $this->routes[$route][$method] = $handler;
    }

    /** @return class-string */
    public function resolveHandler(): string
    {
        if (!isset($this->request)) {
            throw new RuntimeException('No request object provided');
        }

        $path = $this->request->getUri()->getPath();
        $method = $this->request->getMethod();

        if (!isset($this->routes[$path])) {
            throw new RuntimeException('No route provided');
        }
        return $this->routes[$path][$method];
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

}