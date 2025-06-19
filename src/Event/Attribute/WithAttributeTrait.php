<?php

namespace Molibdenius\CQRS\Event\Attribute;

use WeakMap;

trait WithAttributeTrait
{
    public function __construct(
        private readonly object $initiator,
        private WeakMap         $storage,
        array                   $attributes = []
    )
    {
        if (!isset($this->storage[$this->initiator])) {
            $this->storage[$this->initiator] = new AttributeBag();
        }

        if (!empty($attributes)) {
            foreach ($attributes as $key => $value) {
                if (in_array($key, AttributeBag::ALLOWED_ATTRIBUTES, true)) {
                    $this->getAttributes()->$key = $value;
                }
            }
        }
    }

    private function getAttributes(): AttributeBag
    {
        return $this->storage[$this->initiator];
    }

    public function getInitiator(): object
    {
        return $this->initiator;
    }

    public function getStorage(): WeakMap
    {
        return $this->storage;
    }
}
