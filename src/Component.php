<?php

declare(strict_types=1);

namespace Molibdenius\CQRS;

use Spiral\RoadRunner\Environment;

/** @psalm-api */
enum Component: string
{
    // CQRS common
    case ActionBus = "action_bus";
    case ActionRegistry = "action_registry";
    case ActionResolver = "action_resolver";
    case HttpDispatcher = "http_dispatcher";
    case QueueDispatcher = "queue_dispatcher";

    // Event system
    case EventDispatcher = "event_dispatcher";
    case CreatingActionListener = "creating_action_listener";
    case CreatingTaskListener = "creating_task_listener";
    case ExecutingActionListener = "executing_action_listener";
    case HandlingErrorListener = "handling_error_listener";
    case LoggingEventsListener = "logging_events_listener";
    case WritingResponseListener = "writing_response_listener";

    // RoadRunner
    case RPC = "rpc";
    case Jobs = "jobs";
    case PSR17Factory = "psr17_factory";
    case PSR7Worker = "psr7_worker";
    case Consumer = "consumer";
    case RoadRunnerLogger = "roadrunner_logger";
    case RoadRunnerEnvironment = "roadrunner_environment";
    case RoadRunnerWorker = "roadrunner_worker";
    case Unknown = 'unknown';

    public static function getDispatcher(Environment $environment): self
    {
        return match (RoadRunnerMode::fromEnv($environment)) {
            RoadRunnerMode::Http => self::HttpDispatcher,
            RoadRunnerMode::Jobs => self::QueueDispatcher,
            default => self::Unknown
        };
    }

}
