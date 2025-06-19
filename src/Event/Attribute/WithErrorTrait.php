<?php declare(strict_types=1);

namespace Molibdenius\CQRS\Event\Attribute;

use Throwable;

/**
 * @method stopPropagation()
 */
trait WithErrorTrait
{
    use WithAttributeTrait;

    public function getError(): ?Throwable
    {
        return $this->getAttributes()->error;
    }

    public function setError(Throwable $error): void
    {
        $this->getAttributes()->error = $error;

        $this->stopPropagation();
    }

    /**
     * @psalm-assert-if-false null $this->getError()
     */
    public function hasError(): bool
    {
        return null !== $this->getAttributes()->error;
    }
}
