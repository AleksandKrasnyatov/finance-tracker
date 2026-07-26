<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler\Reminder;

use SergiX44\Nutgram\Nutgram;

final class ReminderCallback
{
    public const string LIST = 'rem:list';
    public const string ON = 'rem:on';
    public const string OFF = 'rem:off';
    public const string TIME = 'rem:time';
    public const string TIMEZONE = 'rem:timezone';
    public const string SET_TIMEZONE = 'rem:set_timezone';

    public static function register(Nutgram $bot): void
    {
        $bot->onCallbackQueryData(self::LIST, [RemindersHandler::class, 'list']);
        $bot->onCallbackQueryData(self::ON, [RemindersHandler::class, 'enable']);
        $bot->onCallbackQueryData(self::OFF, [RemindersHandler::class, 'disable']);
        $bot->onCallbackQueryData(self::TIME, [RemindersHandler::class, 'askTime']);
        $bot->onCallbackQueryData(self::TIMEZONE, [RemindersHandler::class, 'timezones']);
        $bot->onCallbackQueryData(
            self::pattern(self::SET_TIMEZONE, 'index'),
            [RemindersHandler::class, 'setTimezone'],
        );
    }

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
