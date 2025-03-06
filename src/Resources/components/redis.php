<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;


use molibdenius\CQRS\Redis\RedisRepository;
use molibdenius\CQRS\Redis\RedisSyncHandler;
use molibdenius\CQRS\Redis\RedisSynchronizer;
use Redis;

return static function (ContainerConfigurator $container) {
    $options = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'connectTimeout' => 2.5,
        'backoff' => [
            'algorithm' => Redis::BACKOFF_ALGORITHM_DECORRELATED_JITTER,
            'base' => 500,
            'cap' => 750,
        ],
    ];

    if (isset($_ENV['REDIS_HOST'])) {
        $options['host'] = $_ENV['REDIS_HOST'];
    }
    if (isset($_ENV['REDIS_PORT'])) {
        $options['port'] = (int)$_ENV['REDIS_PORT'];
    }
    if (isset($_ENV['REDIS_PASSWORD'], $_ENV['REDIS_USERNAME'])) {
        $options['auth'] = [
            $_ENV['REDIS_USERNAME'],
            $_ENV['REDIS_PASSWORD']
        ];
    }
    $container->services()
        ->set('redis', Redis::class)
        ->args([$options])
        ->alias(Redis::class, 'redis')->public()
        ->set('redis.repository', RedisRepository::class)
        ->autowire()
        ->autoconfigure()
        ->alias(RedisRepository::class, 'redis.repository')->public()
        ->set('redis.sync', RedisSynchronizer::class)
        ->autowire()
        ->autoconfigure()
        ->alias(RedisSynchronizer::class, 'redis.sync')
        ->set(RedisSyncHandler::class)
        ->autowire()
        ->autoconfigure()
        ->tag('cqrs.handler');
};