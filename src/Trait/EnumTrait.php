<?php

namespace molibdenius\CQRS\Trait;

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
}