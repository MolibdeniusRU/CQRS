<?php

namespace molibdenius\CQRS\Handler;

use molibdenius\CQRS\Action\Action;

/**
 * Интерфейс для интеграции пользовательского кода обработки действий.
 *
 * @template TAction of Action
 */
interface Handler
{
    /**
     * Обрабатывает действие.
     * @param TAction $action
     */
    public function handle(Action $action): mixed;
}