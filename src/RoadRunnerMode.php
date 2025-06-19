<?php declare(strict_types=1);

namespace Molibdenius\CQRS;


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

    public static function fromEnv(Environment $environment): self
    {
        return self::tryFrom($environment->getMode()) ?? self::Unknown;
    }
}

