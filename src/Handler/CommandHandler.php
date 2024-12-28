<?php

namespace molibdenius\CQRS\Handler;

use molibdenius\CQRS\Action\Action;

interface CommandHandler extends Handler
{
    public function handle(Action $action): bool;

}