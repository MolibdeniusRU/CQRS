<?php

namespace molibdenius\CQRS\Action;

use molibdenius\CQRS\Action\Enum\ActionState;
use molibdenius\CQRS\Action\Enum\ActionType;

class ActionFactory
{
    /**
     * @template TAction of Action
     * @param class-string<TAction> $actionClass
     * @param array<mixed, mixed> $payload
     * @return TAction
     */
    public static function create(string $actionClass, ActionType $type, array $payload = []): Action
    {
        $action = new $actionClass();
        $action->setActionType($type);
        $action->setActionState(ActionState::New);
        $action->load($payload);

        return $action;
    }
}