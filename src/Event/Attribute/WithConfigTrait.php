<?php

namespace Molibdenius\CQRS\Event\Attribute;

use Molibdenius\CQRS\Registry\ActionConfig;

trait WithConfigTrait
{
    use WithAttributeTrait;

    public function getConfig(): ?ActionConfig
    {
        return $this->getAttributes()->config;
    }

    public function setConfig(ActionConfig $config): void
    {
        $this->getAttributes()->config = $config;
    }

    /**
     * @psalm-assert-if-true !null $this->getConfig()
     */
    public function hasConfig(): bool
    {
        return null !== $this->getAttributes()->config;
    }
}
