<?php

namespace molibdenius\CQRS\Enum;

use molibdenius\CQRS\Trait\EnumTrait;

enum PayloadType: string
{
    use EnumTrait;

    case Body = "body";

    case Query = "query";

    case File = "file";
}
