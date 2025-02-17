<?php

namespace molibdenius\CQRS\Router;

use molibdenius\CQRS\EnumTrait;

enum HttpMethod: string
{
    use EnumTrait;
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
    case OPTIONS = 'OPTIONS';
}
