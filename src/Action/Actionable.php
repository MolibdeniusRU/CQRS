<?php

namespace molibdenius\CQRS\Action;


use molibdenius\CQRS\Action\Enum\ActionState;
use molibdenius\CQRS\Action\Enum\ActionType;
use molibdenius\CQRS\Action\Enum\PayloadType;
use ReflectionClass;
use ReflectionProperty;


trait Actionable
{
    private ActionState $actionState;

    private ActionType $actionType;

    /** @var PayloadType[] */
    private array $actionPayloadTypes;

    public function getActionState(): ActionState
    {
        return $this->actionState;
    }

    public function setActionState(ActionState $actionState): void
    {
        $this->actionState = $actionState;
    }

    public function getActionType(): ActionType
    {
        return $this->actionType;
    }

    public function setActionType(ActionType $actionType): void
    {
        $this->actionType = $actionType;
    }

    public function getActionPayloadTypes(): array
    {
        return $this->actionPayloadTypes;
    }

    /**
     * @param PayloadType[] $actionPayloadTypes
     */
    public function setActionPayloadTypes(array $actionPayloadTypes): void
    {
        $this->actionPayloadTypes = $actionPayloadTypes;
    }

    public function load(array $attributes): void
    {
        $reflection = new ReflectionClass($this);

        $_attributes = $this->getObjectProperties($reflection);

        foreach ($attributes as $name => $value) {
            if (isset($_attributes[$name])) {
                if (is_array($_attributes[$name])) {
                    $method = $_attributes[$name][$name];
                    $this->$method($value);
                } else {
                    $this->$name = $value;
                }

            }
        }
    }

    private function getObjectProperties(ReflectionClass $reflection): array
    {
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        $attributesDefinition = [];
        foreach ($properties as $property) {
            $methodName = 'set' . ucfirst($property->getName());
            if ($reflection->hasMethod($methodName)) {
                $attributesDefinition[$property->getName()] = [$property->getName() => $methodName];
            } else {
                $attributesDefinition[$property->getName()] = $property->getName();
            }
        }
        return $attributesDefinition;
    }
}