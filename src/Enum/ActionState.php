<?php

namespace molibdenius\CQRS\Enum;

use molibdenius\CQRS\Trait\EnumTrait;

enum ActionState: string
{
    use EnumTrait;
    case NEW = 'new';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';
}
