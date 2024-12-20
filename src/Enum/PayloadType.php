<?php

namespace molibdenius\CQRS\Enum;

use molibdenius\CQRS\Trait\EnumTrait;

enum PayloadType: string
{
    use EnumTrait;

    case BODY = "body";

    case QUERY = "query";

    case FILE = "file";
}
