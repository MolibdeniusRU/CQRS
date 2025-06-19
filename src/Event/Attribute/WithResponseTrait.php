<?php

namespace Molibdenius\CQRS\Event\Attribute;

use Psr\Http\Message\ResponseInterface;

trait WithResponseTrait
{
    use WithAttributeTrait;

    public function setResponse(ResponseInterface $response): void
    {
        $this->getAttributes()->response = $response;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->getAttributes()->response;
    }

    /**
     * @psalm-assert-if-true !null $this->getResponse()
     */
    public function hasResponse(): bool
    {
        return null !== $this->getAttributes()->response;
    }
}