<?php

namespace molibdenius\CQRS\Attribute;

use Attribute;
use molibdenius\CQRS\Enum\ActionType;
use molibdenius\CQRS\Enum\PayloadType;
use RuntimeException;

#[Attribute]
class ActionHandler
{
    /** @var class-string */
    public string $actionClass;

    public string $path;

    public string $method;

    public string $payloadType;

    public string $type;

    /** @var string[] */
    private array $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE',];

    public function __construct(string $actionClass, string $path, string $method, string $payloadType, string $type)
    {
        $this->actionClass = $actionClass;
        $this->path = $path;

        if (!in_array($method, $this->allowedMethods, true)) {
            throw new RuntimeException("Method '{$method}' is not allowed");
        }

        $this->method = $method;

        if (!in_array($type, ActionType::getValues(), true)) {
            throw new RuntimeException('Invalid action type');
        }

        if (!in_array($payloadType, PayloadType::getValues(), true)) {
            throw new RuntimeException('Invalid payload type');
        }

        $this->payloadType = $payloadType;

        $this->type = $type;
    }
}