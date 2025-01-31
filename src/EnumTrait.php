<?php

namespace molibdenius\CQRS;

trait EnumTrait
{
    /** @return array<mixed> */
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** @return array<mixed> */
    public static function getNames(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function get(string $name): static
    {
        return self::tryFrom($name);
    }
}