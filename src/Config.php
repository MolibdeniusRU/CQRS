<?php

namespace molibdenius\CQRS;

use Symfony\Component\Serializer\Attribute\SerializedPath;

class Config
{
    #[SerializedPath('[connection][host]')]
    public string $host;
    #[SerializedPath('[connection][port]')]
    public string $port;

    /** @var string[] */
    #[SerializedPath('[handlers_dirs]')]
    public array $handlers_dirs = [];

}