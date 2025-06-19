<?php declare(strict_types=1);

namespace Molibdenius\CQRS\Registry;

use Attribute;
use Molibdenius\CQRS\Exception\RegistryException;
use Throwable;

#[Attribute(Attribute::TARGET_CLASS)]
final class ActionConfig
{
    /**
     * @var array<class-string<Throwable>, ActionRetry>
     */
    public array $retries = [];

    /**
     * @param non-empty-string|null $name
     * @param ActionRetry[] $retries
     * @throws RegistryException
     */
    public function __construct(
        public ActionType   $type,
        public ?string      $name = null,
        public ?PayloadType $payloadType = null,
        public ?string      $asyncMessage = null,
        array               $retries = [],
    )
    {
        if (!empty($retries)) {
            foreach ($retries as $retry) {
                if (!$retry instanceof ActionRetry) {
                    throw new RegistryException("Class %s must be ActionRetry");
                }

                $this->retries[$retry->exception] = $retry;
            }
        }
    }
}
