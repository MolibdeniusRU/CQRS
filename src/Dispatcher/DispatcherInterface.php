<?php declare(strict_types=1);

namespace Molibdenius\CQRS\Dispatcher;

interface DispatcherInterface
{
    /**
     * Start serving the dispatcher.
     *
     * This method will block until the dispatcher is stopped.
     */
    public function serve(): void;
}
