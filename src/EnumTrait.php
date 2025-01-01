<?php

namespace molibdenius\CQRS;

/** @method static cases() */
trait EnumTrait
{
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getNames(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function get(string $name): self
    {
        return self::tryFrom($name);
    }
}