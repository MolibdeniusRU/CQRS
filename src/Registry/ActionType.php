<?php declare(strict_types=1);

namespace Molibdenius\CQRS\Registry;

enum ActionType: string
{
    case Command = 'command';

    case Query = 'query';

    case Unknown = 'unknown';
}
