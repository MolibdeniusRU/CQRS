<?php

namespace molibdenius\CQRS\Action;

use molibdenius\CQRS\Action\Enum\ActionState;
use molibdenius\CQRS\Action\Enum\ActionType;
use molibdenius\CQRS\Action\Enum\PayloadType;

/**
 * Интерфейс, содержащий методы для автоматического управления действиями, для интеграции пользовательских действий.
 */
interface Action
{
    /**
     * Получить состояние действия.
     */
    public function getActionState(): ActionState;

    /**
     * Задать состояние действия.
     */
    public function setActionState(ActionState $actionState): void;

    /**
     * Получить тип действия.
     */
    public function getActionType(): ActionType;

    /**
     * Задать тип действия.
     */
    public function setActionType(ActionType $actionType): void;

    /**
     * Метод для загрузки атрибутов интеграции.
     *
     * @param mixed[] $attributes
     */
    public function load(array $attributes): void;
}