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
     *
     * @return ActionState
     */
    public function getActionState(): ActionState;

    /**
     * Задать состояние действия.
     *
     * @param ActionState $state
     * @return void
     */
    public function setActionState(ActionState $state): void;

    /**
     * Получить тип действия.
     *
     * @return ActionType
     */
    public function getActionType(): ActionType;

    /**
     * Задать тип действия.
     *
     * @param ActionType $type
     * @return void
     */
    public function setActionType(ActionType $type): void;

    /**
     * Получить тип передачи полезной нагрузки.
     *
     * @return PayloadType
     */
    public function getActionPayloadType(): PayloadType;

    /**
     * Задать тип полезной нагрузки.
     *
     * @param PayloadType $payloadType
     * @return void
     */
    public function setActionPayloadType(PayloadType $payloadType): void;

    /**
     * Метод для загрузки атрибутов интеграции.
     *
     * @param mixed[] $attributes
     */
    public function load(array $attributes): void;
}