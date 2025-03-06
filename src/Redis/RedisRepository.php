<?php

namespace molibdenius\CQRS\Redis;

use molibdenius\CQRS\Redis\Attribute\ReadModelID;
use Redis;
use RedisException;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use WS\Utils\Collections\Collection;
use WS\Utils\Collections\CollectionFactory;

readonly class RedisRepository
{
    public function __construct(
        private Redis               $redis,
        private SerializerInterface $serializer,
    )
    {
    }

    /**
     * @throws ExceptionInterface
     * @throws RedisException
     */
    public function save(object $entity, string $collectionName = null, ?int $ttl = null): string|false
    {
        $key = $this->buildKey($entity, $collectionName);
        $data = $this->serializer->serialize(
            $entity,
            'json',
            ['groups' => [$collectionName ? 'read:' . $collectionName : 'read']]);

        if (!$this->redis->set($key, $data)) {
            return false;
        }

        if ($ttl !== null) {
            $this->redis->expire($key, $ttl);
        }

        return $key;
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @throws ExceptionInterface
     * @throws RedisException
     */
    public function find(string $className, string $id, bool $asObject = false): string|object|false
    {
        if (is_string($data = $this->redis->get("$className:$id"))) {
            if ($asObject) {
                $data = $this->serializer->deserialize($data, $className, 'json');
            }

            return $data;
        }

        return false;
    }

    /**
     * @throws RedisException
     */
    public function delete(object $entity, string $collectionName): Redis|int|false
    {
        return $this->redis->del($this->buildKey($entity, $collectionName));
    }

    /**
     * @throws RedisException
     */
    public function exists(object $entity, string $collectionName = null, string $type = 'entity'): bool|int|Redis
    {
        $key = $this->buildKey($entity, $collectionName);

        if ($type === 'collection') {
            $key = $this->getCollectionKey($collectionName, $entity::class);
        }

        return $this->redis->exists($key);
    }

    /**
     * @param array<object> $collection
     * @throws RedisException
     * @throws ExceptionInterface
     */
    public function saveCollection(string $collectionName, array $collection): void
    {
        $this->redis->multi();

        foreach ($collection as $entity) {
            $this->addToCollection($entity, $collectionName);
        }

        $this->redis->exec();

    }

    /**
     * @throws RedisException
     * @throws ExceptionInterface
     */
    public function addToCollection(object $entity, string $collectionName): void
    {
        $entityKey = $this->save($entity, $collectionName);

        $this->redis->sAdd(
            $this->getCollectionKey($collectionName, $entity::class),
            $entityKey
        );
    }

    /**
     * @param string $collectionName
     * @param string $className
     * @param bool $asObject
     * @return string|Collection
     * @throws RedisException
     */
    public function getCollection(string $collectionName, string $className, bool $asObject = false): string|Collection
    {
        $members = $this->redis->mGet($this->redis->sMembers($this->getCollectionKey($collectionName, $className)));
        $members = is_array($members) ? $members : [];

        if ($asObject) {
            $members = $this->createObjectCollection($members, $className);
        } else {
            $members = $this->asJson($members);
        }

        return $members;
    }

    /**
     * @param array<string> $members
     */
    private function createObjectCollection(array $members, string $className): Collection
    {
        if (empty($members)) {
            return CollectionFactory::empty();
        }

        return CollectionFactory::from($members)
            ->stream()
            ->map(fn($data) => $data ?
                $this->serializer->deserialize($data, $className, 'json') :
                null
            )
            ->getCollection();
    }

    /**
     * @param array<string> $members
     */
    private function asJson(array $members): string
    {
        return "[" . implode(",", $members) . "]";
    }

    /**
     * @throws RedisException
     */
    public function removeFromCollection(object $entity, string $collectionName): void
    {
        $this->delete($entity, $collectionName);

        $this->redis->sRem(
            $this->getCollectionKey($collectionName, $entity::class),
            $this->buildKey($entity, $collectionName)
        );
    }

    private function buildKey(object $entity, string $collectionName = null): string
    {
        $idProperty = $this->getIdProperty($entity);
        $name = $entity::class;

        if ($collectionName !== null) {
            $name = $collectionName . ':' . $name;
        }

        return $name . ':' . $idProperty->getValue($entity);
    }

    private function getCollectionKey(string $collectionName, string $className): string
    {
        return "$className:collection:$collectionName";
    }

    private function getIdProperty(object $entity): ReflectionProperty
    {
        $reflection = new ReflectionClass($entity);

        $idProperty = CollectionFactory::from($reflection->getProperties())
            ->stream()
            ->findFirst(function (ReflectionProperty $property) {
                return !empty($property->getAttributes(ReadModelID::class));
            });

        if ($idProperty === null) {
            throw new \RuntimeException(sprintf('Entity %s has no ReadModelID attribute', $reflection->getName()));
        }

        return $idProperty;
    }
}