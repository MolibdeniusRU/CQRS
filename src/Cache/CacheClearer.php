<?php

namespace molibdenius\CQRS\Cache;

use InvalidArgumentException;
use Psr\Cache\CacheItemPoolInterface;

class CacheClearer
{
    /** @var CacheItemPoolInterface[] */
    private array $pools;

    /**
     * @param array<string, CacheItemPoolInterface> $pools
     */
    public function __construct(array $pools = [])
    {
        $this->pools = $pools;
    }

    public function hasPool(string $name): bool
    {
        return isset($this->pools[$name]);
    }

    /**
     * @throws InvalidArgumentException If the cache pool with the given name does not exist
     */
    public function getPool(string $name): CacheItemPoolInterface
    {
        if (!$this->hasPool($name)) {
            throw new InvalidArgumentException(\sprintf('Cache pool not found: "%s".', $name));
        }

        return $this->pools[$name];
    }

    /**
     * @throws InvalidArgumentException If the cache pool with the given name does not exist
     */
    public function clearPool(string $name): bool
    {
        if (!isset($this->pools[$name])) {
            throw new InvalidArgumentException(\sprintf('Cache pool not found: "%s".', $name));
        }

        return $this->pools[$name]->clear();
    }

    public function clear(): void
    {
        foreach ($this->pools as $pool) {
            $pool->clear();
        }
    }
}