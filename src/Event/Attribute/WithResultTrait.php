<?php declare(strict_types=1);

namespace Molibdenius\CQRS\Event\Attribute;

use Molibdenius\CQRS\Result\Result;

/**
 * @method stopPropagation()
 */
trait WithResultTrait
{
    use WithAttributeTrait;

    public function getResult(): ?Result
    {
        return $this->getAttributes()->result;
    }

    public function setResult(Result $result): void
    {
        $this->getAttributes()->result = $result;
    }

    /**
     * @psalm-assert-if-true !null $this->getResult()
     */
    public function hasResult(): bool
    {
        return null !== $this->getAttributes()->result;
    }
}
