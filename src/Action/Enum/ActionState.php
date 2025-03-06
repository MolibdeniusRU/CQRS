<?php

namespace molibdenius\CQRS\Action\Enum;

enum ActionState: string
{
    case New = 'new';
    case Completed = 'completed';
    case Rejected = 'rejected';
}
