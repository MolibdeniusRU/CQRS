<?php

namespace molibdenius\CQRS;

interface CQRSKernelInterface
{
    public function serve(): void;
}