<?php

namespace molibdenius\CQRS\Action\Enum;

use molibdenius\CQRS\EnumTrait;

enum ActionState: string
{
    use EnumTrait;
    case New = 'new';
    case Completed = 'completed';
    case Rejected = 'rejected';
}
