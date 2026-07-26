<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler\Transaction;

use SergiX44\Nutgram\Nutgram;

final class TransactionCallback
{
    public const string LIST = 'txn:list';
    public const string MONTH = 'txn:month';
    public const string VIEW = 'txn:view';
    public const string BACK = 'txn:back';

    public const string FILTER_ALL = 'all';
    public const string FILTER_EXPENSE = 'expense';
    public const string FILTER_INCOME = 'income';

    public static function register(Nutgram $bot): void
    {
        $bot->onCallbackQueryData(
            self::pattern(self::LIST, 'year', 'month', 'filter'),
            [TransactionsListHandler::class, 'list'],
        );
        $bot->onCallbackQueryData(
            self::pattern(self::MONTH, 'year', 'month'),
            [TransactionsListHandler::class, 'month'],
        );
        $bot->onCallbackQueryData(
            self::pattern(self::VIEW, 'id'),
            [TransactionsListHandler::class, 'view'],
        );
        $bot->onCallbackQueryData(self::BACK, [TransactionsListHandler::class, 'back']);
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
