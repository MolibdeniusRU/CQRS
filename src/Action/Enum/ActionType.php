<?php

namespace molibdenius\CQRS\Action\Enum;

use molibdenius\CQRS\EnumTrait;

enum ActionType: string
{
    use EnumTrait;
    case Command = 'command';
    case Query = 'query';
}
