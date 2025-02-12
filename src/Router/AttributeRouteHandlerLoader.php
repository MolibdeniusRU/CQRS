<?php

namespace molibdenius\CQRS\Router;

use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Handler\Attribute\ActionHandler;
use molibdenius\CQRS\Handler\Handler;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Routing\Loader\AttributeClassLoader;
use Symfony\Component\Routing\Route;
use WS\Utils\Collections\ArrayList;
use WS\Utils\Collections\Functions\Reorganizers;

class AttributeRouteHandlerLoader extends AttributeClassLoader
{
    /**
     * @param ReflectionClass<Handler<Action>> $class
     */
    protected function configureRoute(Route $route, ReflectionClass $class, ReflectionMethod $method, object $attr): void
    {
        $route->setDefault('_handler', $class->getName());

        $action = ArrayList::of($class->getAttributes())->stream()
            ->reorganize(Reorganizers::collapse())
            ->findFirst(
                function (ReflectionAttribute $reflectionAttribute) {
                    return $reflectionAttribute->newInstance() instanceof ActionHandler;
                }
            )->newInstance()->actionClass;
        $route->addDefaults(['_action' => $action]);
    }
}