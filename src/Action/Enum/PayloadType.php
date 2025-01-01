<?php

namespace molibdenius\CQRS\Action\Enum;

use molibdenius\CQRS\EnumTrait;

enum PayloadType: string
{
    use EnumTrait;

    case Body = "body";

    case Query = "query";

    case File = "file";
}
