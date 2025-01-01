<?php

namespace molibdenius\CQRS\Action;


use molibdenius\CQRS\Action\Enum\ActionState;
use molibdenius\CQRS\Action\Enum\ActionType;
use molibdenius\CQRS\Action\Enum\PayloadType;
use ReflectionClass;
use ReflectionProperty;

trait ActionTrait
{
    private ActionState $state;

    private ActionType $type;

    private PayloadType $payloadType;

    public function getState(): ActionState
    {
        return $this->state;
    }

    public function setState(ActionState $state): void
    {
        $this->state = $state;
    }

    public function getType(): ActionType
    {
        return $this->type;
    }

    public function setType(ActionType $type): void
    {
        $this->type = $type;
    }

    public function getPayloadType(): PayloadType
    {
        return $this->payloadType;
    }

    public function setPayloadType(PayloadType $payloadType): void
    {
        $this->payloadType = $payloadType;
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