<?php

declare(strict_types=1);

namespace Molibdenius\CQRS\Result;

final class ProcessInfo extends AbstractResult
{
    public function __construct(
        string $name,
        string $message,
        string $taskId
    )
    {
        parent::__construct([
            'name' => $name,
            'message' => $message,
            'task_id' => $taskId
        ]);
    }
}
