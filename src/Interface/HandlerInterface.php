<?php

namespace molibdenius\CQRS\Interface;

interface HandlerInterface
{
    public function handle(ActionInterface $action): mixed;
}