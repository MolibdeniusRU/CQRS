<?php

namespace molibdenius\CQRS\Router;

use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router as SymfonyRouter;

final class Router extends SymfonyRouter
{
    /**
     * @return mixed[]
     */
    public function resolveRouteParams(ServerRequestInterface $request): array
    {
        $this->setContext(new RequestContext(
            baseUrl: $request->getUri(),
            method: $request->getMethod(),
            host: $request->getUri()->getHost(),
            scheme: $request->getUri()->getScheme(),
            path: $request->getUri()->getPath(),
            queryString: $request->getUri()->getQuery()
        ));

        return $this->match($request->getUri()->getPath());
    }

    /**
     * @throws Exception
     */
    public function getRouteCollection(): RouteCollection
    {
        if (isset($this->collection)) {
            return $this->collection;
        }

        $this->collection = new RouteCollection();

        foreach ($this->resource as $class) {
            $this->collection->addCollection($this->loader->load($class));
        }

        return $this->collection;
    }

    public function setResource(mixed $resource): void
    {
        $this->resource = $resource;
    }

}