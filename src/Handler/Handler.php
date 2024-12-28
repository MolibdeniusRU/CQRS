<?php

namespace molibdenius\CQRS\Handler;

use molibdenius\CQRS\Action\Action;

interface Handler
{
    public function handle(Action $action): mixed;
}