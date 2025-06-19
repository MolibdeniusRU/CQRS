<?php declare(strict_types=1);

namespace Molibdenius\CQRS\Result;

interface Result
{
    public function getContent(): mixed;

    public function getCode(): int;
}
