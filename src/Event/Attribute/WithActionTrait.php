<?php declare(strict_types=1);

namespace Molibdenius\CQRS\Event\Attribute;

use Molibdenius\CQRS\Action;

trait WithActionTrait
{
    use WithAttributeTrait;

    public function getAction(): ?Action
    {
        return $this->getAttributes()->action;
    }

    public function setAction(Action $action): void
    {
        $this->getAttributes()->action = $action;
    }

    /**
     * @psalm-assert-if-true !null $this->getAction()
     */
    public function hasAction(): bool
    {
        return null !== $this->getAttributes()->action;
    }
}
