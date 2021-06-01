<?php

abstract class BasicEnum
{
    private static $cache = [];

    public static function isValidName(string $name): bool
    {
        return array_key_exists($name, self::getConstants());
    }

    public static function getConstants(): array
    {
        if (! array_key_exists(static::class, self::$cache)) {
            self::$cache[static::class] = (new ReflectionClass(static::class))->getConstants();
        }

        return self::$cache[static::class];
    }

    public static function isValidValue($value): bool
    {
        return in_array($value, self::getConstants(), true);
    }
}