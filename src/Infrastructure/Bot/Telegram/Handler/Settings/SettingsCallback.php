<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler\Settings;

use SergiX44\Nutgram\Nutgram;

final class SettingsCallback
{
    public const string LIST = 'settings:list';
    public const string LANGUAGE = 'settings:language';
    public const string SET_LANGUAGE = 'settings:set_language';
    public const string NOTIFICATIONS = 'settings:notifications';

    public static function register(Nutgram $bot): void
    {
        $bot->onCallbackQueryData(self::LIST, [SettingsHandler::class, 'list']);
        $bot->onCallbackQueryData(self::LANGUAGE, [SettingsHandler::class, 'language']);
        $bot->onCallbackQueryData(
            self::pattern(self::SET_LANGUAGE, 'locale'),
            [SettingsHandler::class, 'setLanguage'],
        );
        $bot->onCallbackQueryData(self::NOTIFICATIONS, [SettingsHandler::class, 'notifications']);
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
