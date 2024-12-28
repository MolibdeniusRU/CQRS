<?php

namespace molibdenius\CQRS\Action;


use ReflectionClass;
use ReflectionProperty;

trait ActionTrait
{
    private string $state;

    private string $type;

    private string $payloadType;

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getPayloadType(): string
    {
        return $this->payloadType;
    }

    public function setPayloadType(string $payloadType): void
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