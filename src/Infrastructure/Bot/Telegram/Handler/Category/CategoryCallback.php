<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler\Category;

use App\Infrastructure\Bot\Telegram\CallbackData;
use SergiX44\Nutgram\Nutgram;

final class CategoryCallback
{
    public const string LIST = 'cat:list';
    public const string TYPE = 'cat:type';
    public const string ADD = 'cat:add';
    public const string VIEW = 'cat:view';
    public const string RENAME = 'cat:rename';
    public const string DELETE = 'cat:delete';
    public const string DELETE_OK = 'cat:delete_ok';

    public static function register(Nutgram $bot): void
    {
        $bot->onCallbackQueryData(self::LIST, [CategoriesListHandler::class, 'list']);
        $bot->onCallbackQueryData(CallbackData::pattern(self::TYPE, 'type'), [CategoriesListHandler::class, 'byType']);
        $bot->onCallbackQueryData(CallbackData::pattern(self::ADD, 'type'), [CategoryAddHandler::class, '__invoke']);
        $bot->onCallbackQueryData(CallbackData::pattern(self::VIEW, 'id'), [CategoryViewHandler::class, '__invoke']);
        $bot->onCallbackQueryData(CallbackData::pattern(self::RENAME, 'id', 'type'), [CategoryRenameHandler::class, '__invoke']);
        $bot->onCallbackQueryData(CallbackData::pattern(self::DELETE, 'id', 'type'), [CategoryDeleteHandler::class, 'confirm']);
        $bot->onCallbackQueryData(CallbackData::pattern(self::DELETE_OK, 'id', 'type'), [CategoryDeleteHandler::class, 'delete']);
    }
}
