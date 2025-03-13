<?php

namespace molibdenius\CQRS;

use Exception;
use molibdenius\CQRS\Dispatcher\DispatcherInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RoadRunner\Logger\Logger;
use Throwable;


abstract class CQRSKernel implements CQRSKernelInterface
{
    private Logger $logger;

    private bool $isInitialized = false;

    private ApplicationMode $applicationMode;

    private ContainerInterface $container;

    public function __construct(?ApplicationMode $applicationMode = null)
    {
        if ($applicationMode === null) {
            $applicationMode = ApplicationMode::Development;
        }

        $this->applicationMode = $applicationMode;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    protected function init(): void
    {
        $this->container = $this->initContainer();

        if (!($logger = $this->container->get(Logger::class)) instanceof Logger) {
            throw new \RuntimeException('Logger service not found');
        }

        $this->logger = $logger;

        $this->isInitialized = true;
    }

    public function serve(): void
    {
        try {
            if (!$this->isInitialized) {
                $this->init();
            }

            $dispatcher = $this->getDispatcher();

            $dispatcher->serve();

        } catch (Throwable $exception) {
            $data = $exception->getMessage();

            if ($this->applicationMode !== ApplicationMode::Development) {
                $data .= PHP_EOL . $exception->getTraceAsString();
            }

            if ($this->isInitialized) {
                $this->logger->error($data);
            } else {
                file_put_contents('php://stderr', $data);
            }
        }
    }

    abstract protected function initContainer(): ContainerInterface;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getDispatcher(): DispatcherInterface
    {
        if (($dispatcherName = Component::getDispatcher()) === Component::Unknown) {
            throw new \RuntimeException('Unknown dispatcher');
        }

        return $this->container->get($dispatcherName->value);
    }


}

