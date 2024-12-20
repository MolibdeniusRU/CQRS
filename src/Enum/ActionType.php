<?php

namespace molibdenius\CQRS\Enum;

use molibdenius\CQRS\Trait\EnumTrait;

enum ActionType: string
{
    use EnumTrait;
    case COMMAND = 'command';
    case QUERY = 'query';
}
