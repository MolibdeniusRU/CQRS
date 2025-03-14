<?php

namespace molibdenius\CQRS;

use Psr\Container\ContainerInterface;


abstract class CQRSKernel implements CQRSKernelInterface
{
    use CQRSKernelTrait;

    private bool $booted = false;

    private ContainerInterface $container;

    public function __construct(private string $environment, private bool $debug = false)
    {
        if (!$this->environment) {
            throw new \InvalidArgumentException(\sprintf('Invalid environment provided to "%s": the environment cannot be empty.', get_debug_type($this)));
        }
    }

    protected function boot(): void
    {
        $this->container = $this->preBoot();

        $this->booted = true;
    }

    abstract protected function preBoot(): ContainerInterface;
}

