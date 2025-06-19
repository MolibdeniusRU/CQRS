<?php

declare(strict_types=1);

namespace Molibdenius\CQRS\Registry;

use Molibdenius\CQRS\Action;
use Molibdenius\CQRS\Exception\ImplementationException;
use Molibdenius\CQRS\Exception\RegistryException;
use ReflectionClass;

final class ActionRegistry implements ActionRegistryInterface
{
    /**
     * @var array<class-string, ActionConfig>
     */
    private array $registry = [];

    /**
     * @param class-string[] $actions
     * @throws ImplementationException
     * @throws RegistryException
     */
    public function __construct(array $actions)
    {
        foreach ($actions as $action) {
            $reflection = new ReflectionClass($action);
            if (!$reflection->implementsInterface(Action::class)) {
                throw new ImplementationException($action, Action::class);
            }

            $attributes = $reflection->getAttributes(ActionConfig::class);

            if (empty($attributes)) {
                throw new RegistryException(sprintf('Class "%s" has no ActionConfig attribute', $action));
            }

            $this->registry[$action] = $attributes[0]->newInstance();
        }
    }

    public function getActionConfig(string $action): ActionConfig
    {
        if (!isset($this->registry[$action])) {
            throw new RegistryException(sprintf('Class %s is not registered as an action', $action));
        }
        return $this->registry[$action];
    }
}
