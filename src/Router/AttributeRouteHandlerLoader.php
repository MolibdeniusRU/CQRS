<?php

namespace molibdenius\CQRS\Router;

use molibdenius\CQRS\Handler\Handler;
use molibdenius\CQRS\Metadata\HandlerMetadata;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Routing\Loader\AttributeClassLoader;
use Symfony\Component\Routing\Route;
use WS\Utils\Collections\CollectionFactory;

class AttributeRouteHandlerLoader extends AttributeClassLoader
{
    /**
     * @param ReflectionClass<Handler> $class
     */
    protected function configureRoute(Route $route, ReflectionClass $class, ReflectionMethod $method, object $attr): void
    {
        $route->setDefault('_handler', $class->getName());

        $action = CollectionFactory::from($class->getAttributes())->stream()
            ->findFirst(
                function (ReflectionAttribute $reflectionAttribute) {
                    return $reflectionAttribute->newInstance() instanceof HandlerMetadata;
                }
            )->newInstance()->actionClass;
        $route->addDefaults(['_action' => $action]);
    }
}