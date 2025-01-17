<?php

namespace molibdenius\CQRS;

enum ApplicationMode: string
{
    use EnumTrait;

    case Development = 'dev';

    case Test = 'test';

    case Production = 'prod';
}
