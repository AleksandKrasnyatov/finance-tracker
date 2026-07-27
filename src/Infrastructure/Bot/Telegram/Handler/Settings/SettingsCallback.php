<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler\Settings;

use App\Infrastructure\Bot\Telegram\CallbackData;
use SergiX44\Nutgram\Nutgram;

final class SettingsCallback
{
    public const string LIST = 'settings:list';
    public const string LANGUAGE = 'settings:language';
    public const string SET_LANGUAGE = 'settings:set_language';

    public static function register(Nutgram $bot): void
    {
        $bot->onCallbackQueryData(self::LIST, [SettingsHandler::class, 'list']);
        $bot->onCallbackQueryData(self::LANGUAGE, [SettingsHandler::class, 'language']);
        $bot->onCallbackQueryData(
            CallbackData::pattern(self::SET_LANGUAGE, 'locale'),
            [SettingsHandler::class, 'setLanguage'],
        );
    }
}
