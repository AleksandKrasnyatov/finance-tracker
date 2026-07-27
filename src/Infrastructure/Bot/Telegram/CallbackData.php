<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram;

final class CallbackData
{
    public static function pattern(string $prefix, string ...$params): string
    {
        $parts = array_map(static fn(string $param): string => '{' . $param . '}', $params);

        return $prefix . ':' . implode(':', $parts);
    }

    public static function data(string $prefix, string ...$values): string
    {
        return $prefix . ':' . implode(':', $values);
    }
}
