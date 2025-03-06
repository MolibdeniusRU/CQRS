<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use molibdenius\CQRS\Component;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyReadInfoExtractorInterface;
use Symfony\Component\PropertyInfo\PropertyWriteInfoExtractorInterface;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(Component::PropertyAccessor->value, PropertyAccessor::class)
        ->args([
            abstract_arg('magic methods allowed, set by the extension'),
            abstract_arg('throw exceptions, set by the extension'),
            service('cache.property_access')->ignoreOnInvalid(),
            service(PropertyReadInfoExtractorInterface::class)->nullOnInvalid(),
            service(PropertyWriteInfoExtractorInterface::class)->nullOnInvalid(),
        ])
        ->alias(PropertyAccessorInterface::class, Component::PropertyAccessor->value);
};