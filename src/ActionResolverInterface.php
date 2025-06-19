<?php

declare(strict_types=1);

namespace Molibdenius\CQRS;

use Molibdenius\CQRS\Exception\ActionNotFoundException;
use Psr\Http\Message\ServerRequestInterface;

interface ActionResolverInterface
{
    /**
     * @return class-string<Action>
     *
     * @throws ActionNotFoundException
     */
    public function resolve(ServerRequestInterface $request): string;
}
