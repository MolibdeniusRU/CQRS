<?php

namespace Molibdenius\CQRS\Exception;

final class ImplementationException extends \Exception implements ExceptionInterface
{
    public function __construct(string $class, string $interface)
    {
        parent::__construct(sprintf('Class "%s" must implement "%s" interface', $class, $interface), 500);
    }
}