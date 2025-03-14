<?php

namespace molibdenius\CQRS;

use molibdenius\CQRS\Dispatcher\DispatcherInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RoadRunner\Logger\Logger;
use Throwable;

trait CQRSKernelTrait
{
    private Logger $logger;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function init(): void
    {
        $this->boot();

        if (!($logger = $this->container->get(Logger::class)) instanceof Logger) {
            throw new \RuntimeException('Logger service not found');
        }

        $this->logger = $logger;
    }

    public function serve(): void
    {
        try {
            if (!$this->booted) {
                $this->init();
            }

            $dispatcher = $this->getDispatcher();

            $dispatcher->serve();

        } catch (Throwable $exception) {
            $data = $exception->getMessage();

            if ($this->debug) {
                $data .= PHP_EOL . $exception->getTraceAsString();
            }

            if ($this->booted) {
                $this->logger->error($data);
            } else {
                file_put_contents('php://stderr', $data);
            }
        }
    }

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