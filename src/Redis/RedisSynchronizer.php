<?php

namespace molibdenius\CQRS\Redis;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use molibdenius\CQRS\Redis\Attribute\ReadCollection;
use ReflectionAttribute;
use ReflectionClass;
use RoadRunner\Logger\Logger;
use RuntimeException;
use WS\Utils\Collections\CollectionFactory;

readonly class RedisSynchronizer
{
    public function __construct(
        private RedisRepository $redisRepository,
        private EntityManager   $entityManager,
        private Logger          $logger
    )
    {
    }

    public function synchronize(object $entity): bool
    {
        try {
            $this->redisRepository->save($entity);

            $reflection = new ReflectionClass($entity);

            CollectionFactory::from($reflection->getAttributes(ReadCollection::class))->stream()
                ->map(
                    function (ReflectionAttribute $reflectionAttribute) use ($entity) {
                        /** @var  ReflectionAttribute<ReadCollection> $reflectionAttribute */

                        $collection = $reflectionAttribute->newInstance();

                        if (!$this->redisRepository->exists($entity, $collection->name, 'collection')) {
                            $repositoryReflection = new ReflectionClass($collection->repository);

                            if (!$repositoryReflection->hasMethod($collection->method)) {
                                throw new RuntimeException(sprintf(
                                    'function %s::%s does not exist',
                                    $collection->repository,
                                    $collection->method
                                ));
                            }

                            if (!$repositoryReflection->isInstance($repository = $this->entityManager->getRepository($entity::class))) {
                                throw new RuntimeException(sprintf('%s must extend %s', $collection->repository, EntityRepository::class));
                            }

                            $this->redisRepository->saveCollection(
                                $collection->name,
                                $repositoryReflection
                                    ->getMethod($collection->method)
                                    ->invoke(
                                        $repository,
                                        ...$collection->arguments
                                    )
                            );
                        }

                        $this->redisRepository->addToCollection($entity, $collection->name);
                    }
                );

            return true;
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage() . '\n' . $exception->getTraceAsString());
            return false;
        }

    }
}