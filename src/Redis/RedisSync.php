<?php

namespace molibdenius\CQRS\Redis;

use molibdenius\CQRS\Action\Action;
use molibdenius\CQRS\Action\Actionable;

class RedisSync implements Action
{
    use Actionable;

    public ?object $entity = null;

}