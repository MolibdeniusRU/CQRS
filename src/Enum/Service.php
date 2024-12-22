<?php

namespace molibdenius\CQRS\Enum;

enum Service: string
{
    case RPC = "rpc";
    case Jobs = "jobs";

    case Environment = "environment";

    case PSR17Factory = "psr17factory";
    case PSR7Worker = "psr7worker";
    case RRWorker = "rr.worker";
    case Router = "router";
    case ActionBus = "action.bus";

}