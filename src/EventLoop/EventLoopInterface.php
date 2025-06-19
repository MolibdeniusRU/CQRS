<?php

namespace Molibdenius\CQRS\EventLoop;

use Molibdenius\CQRS\Exception\EventException;

/**
 * @template TInitiator of object
 *
 * @template TResult
 */
interface EventLoopInterface
{
    /**
     * @param TInitiator $initiator
     * @return TResult
     *
     * @throws EventException
     */
    public function run(object $initiator);
}
