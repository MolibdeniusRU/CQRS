<?php declare(strict_types=1);

namespace Molibdenius\CQRS\Registry;

enum PayloadType: string
{
    case QueryString = 'query_string';

    case Body = 'body';

    case Form = 'form';
}
