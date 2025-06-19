<?php declare(strict_types=1);

namespace Molibdenius\CQRS\Result;

class AbstractResult implements Result
{
    public function __construct(
        private readonly mixed $content,
        private readonly int   $code = 200,
    )
    {
    }

    public function getContent(): mixed
    {
        return $this->content;
    }

    public function getCode(): int
    {
        return $this->code;
    }
}
