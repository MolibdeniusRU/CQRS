<?php

namespace molibdenius\CQRS;


enum RoadRunnerMode: string
{
    use EnumTrait;
    case Http = 'http';
    case Jobs = 'jobs';
    case Temporal = 'temporal';
    case Grpc = 'grpc';
    case Tcp = 'tcp';
    case Centrifuge = 'centrifuge';
    case Unknown = 'unknown';
}

