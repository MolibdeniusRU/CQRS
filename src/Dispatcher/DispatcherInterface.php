<?php

namespace molibdenius\CQRS\Dispatcher;

use Spiral\RoadRunner\EnvironmentInterface;

interface DispatcherInterface
{
    /**
     * Проверяет режим RoadRunner`а на соответствие типа диспетчера.
     *
     * @param EnvironmentInterface $env
     * @return bool
     */
    public function canServe(EnvironmentInterface $env): bool;

    /**
     * Запускает диспетчер в режим ожидания запросов.
     */
    public function serve(): void;
}