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
     *
     * @param Action $action
     * @return mixed
     */
    public function handle(Action $action): mixed;
}