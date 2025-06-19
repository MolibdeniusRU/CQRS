<?php

namespace Molibdenius\CQRS\Event\Attribute;

trait WithDebugTrait
{
    use WithAttributeTrait;

    public function isDebug(): bool
    {
        return $this->getAttributes()->debug;
    }
}
