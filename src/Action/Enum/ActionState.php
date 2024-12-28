<?php

namespace molibdenius\CQRS\Action\Enum;

use molibdenius\CQRS\Trait\EnumTrait;

enum ActionState: string
{
    use EnumTrait;
    case New = 'new';
    case Completed = 'completed';
    case Rejected = 'rejected';
}
