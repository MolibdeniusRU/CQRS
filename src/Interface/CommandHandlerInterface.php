<?php

namespace molibdenius\CQRS\Interface;

use molibdenius\CQRS\Interface\HandlerInterface;

interface CommandHandlerInterface extends HandlerInterface
{
    public function handle(ActionInterface $action): bool;

}