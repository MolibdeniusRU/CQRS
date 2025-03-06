<?php

namespace molibdenius\CQRS\Handler;

use molibdenius\CQRS\Action\Action;

/**
 * Интерфейс для интеграции пользовательского кода обработки действий.
 */
interface Handler
{
    /**
     * Обрабатывает действие.
     * @template TAction of Action
     * @param TAction $action
     */
    public function handle(Action $action): mixed;
}