<?php

namespace molibdenius\CQRS;

enum Service: string
{
    case RPC = "rpc";
    case Jobs = "jobs";

    case Environment = "environment";

    case PSR17Factory = "psr17_factory";
    case PSR7Worker = "psr7_worker";
    case RRWorker = "rr_worker";
    case Router = "router";
    case ActionBus = "action_bus";

    case ServiceContainer = "service_container";

}