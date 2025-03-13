<?php

namespace molibdenius\CQRS;


use Spiral\RoadRunner\Environment;

enum RoadRunnerMode: string
{
    case Http = 'http';
    case Jobs = 'jobs';
    case Temporal = 'temporal';
    case Grpc = 'grpc';
    case Tcp = 'tcp';
    case Centrifuge = 'centrifuge';
    case Unknown = 'unknown';

    public static function fromEnv(): self
    {
        return self::tryFrom(Environment::fromGlobals()->getMode()) ?? self::Unknown;
    }
}

