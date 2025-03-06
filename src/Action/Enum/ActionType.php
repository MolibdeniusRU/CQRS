<?php

namespace molibdenius\CQRS\Action\Enum;

enum ActionType: string
{
    case Command = 'command';

    case Query = 'query';
}
