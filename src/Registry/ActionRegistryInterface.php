<?php declare(strict_types=1);

namespace Molibdenius\CQRS\Registry;

use Molibdenius\CQRS\Action;
use Molibdenius\CQRS\Exception\RegistryException;

interface ActionRegistryInterface
{
    /**
     * @param class-string<Action> $action
     *
     * @throws RegistryException
     */
    public function getActionConfig(string $action): ActionConfig;
}
