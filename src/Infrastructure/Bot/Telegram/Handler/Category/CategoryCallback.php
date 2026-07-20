<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler\Category;

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
        $bot->onCallbackQueryData(self::pattern(self::TYPE, 'type'), [CategoriesListHandler::class, 'byType']);
        $bot->onCallbackQueryData(self::pattern(self::ADD, 'type'), [CategoryAddHandler::class, '__invoke']);
        $bot->onCallbackQueryData(self::pattern(self::VIEW, 'id'), [CategoryViewHandler::class, '__invoke']);
        $bot->onCallbackQueryData(self::pattern(self::RENAME, 'id', 'type'), [CategoryRenameHandler::class, '__invoke']);
        $bot->onCallbackQueryData(self::pattern(self::DELETE, 'id', 'type'), [CategoryDeleteHandler::class, 'confirm']);
        $bot->onCallbackQueryData(self::pattern(self::DELETE_OK, 'id', 'type'), [CategoryDeleteHandler::class, 'delete']);
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
