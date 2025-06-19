<?php declare(strict_types=1);

namespace Molibdenius\CQRS\ActionBus;

use Molibdenius\CQRS\Action;
use Molibdenius\CQRS\Exception\ImplementationException;
use Molibdenius\CQRS\Handler;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final readonly class ActionBus implements ActionBusInterface
{
    public function __construct(
        private ContainerInterface $handlers,
    )
    {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ImplementationException
     */
    public function dispatch(Action $action): mixed
    {

        $handler = $this->handlers->get($action::class);

        if (!$handler instanceof Handler) {
            throw new ImplementationException($handler::class, Handler::class);
        }

        return $handler->handle($action);
    }
}
