<?php

namespace molibdenius\CQRS\Enum;

use molibdenius\CQRS\Trait\EnumTrait;

enum ActionType: string
{
    use EnumTrait;
    case Command = 'command';
    case Query = 'query';
}
