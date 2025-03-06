<?php

namespace molibdenius\CQRS;

enum ApplicationMode: string
{
    case Development = 'dev';

    case Test = 'test';

    case Production = 'prod';
}
