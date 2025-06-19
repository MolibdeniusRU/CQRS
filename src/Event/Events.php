<?php

declare(strict_types=1);

namespace Molibdenius\CQRS\Event;

enum Events: string
{
    case Request = 'cqrs.request';

    case HttpError = 'cqrs.http.error';

    case NewAction = 'cqrs.new.action';

    case Response = 'cqrs.response';

    case NewTask = 'cqrs.new.task';

    case RetryTask = 'cqrs.retry.task';

    public static function getLogView(self $event): string
    {
        $logBody = match ($event) {
            self::Request => ' [request] %s %s',
            self::HttpError => ' [message] %s',
            self::NewAction => ' [action] %s [type] %s',
            self::NewTask => ' [task] %s [id] %s',
            self::RetryTask => ' [queue] %s [task] %s [error] %s',
            self::Response => ' [response] %s',
        };

        return sprintf('[Event %s]', $event->name) . $logBody;
    }

    public static function getAliases(): array
    {
        return [
            ResponseEvent::class => self::Response->value,
            RequestEvent::class => self::Request->value,
            NewActionEvent::class => self::NewAction->value,
            NewTaskEvent::class => self::NewTask->value,
            RetryTaskEvent::class => self::RetryTask->value,
            HttpErrorEvent::class => self::HttpError->value,
        ];
    }
}
