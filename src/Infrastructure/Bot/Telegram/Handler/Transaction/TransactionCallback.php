<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler\Transaction;

use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Nutgram;

final class TransactionCallback
{
    public const string LIST = 'transaction:list';
    public const string MONTH = 'transaction:month';
    public const string VIEW = 'transaction:view';
    public const string MONEY = 'transaction:money';
    public const string DATE = 'transaction:date';
    public const string DESCRIPTION = 'transaction:description';
    public const string CATEGORY = 'transaction:cat';
    public const string SET_CATEGORY = 'transaction:set_cat';
    public const string DELETE = 'transaction:delete';
    public const string DELETE_OK = 'transaction:delete_ok';
    public const string BACK = 'transaction:back';

    public const string FILTER_ALL = 'all';
    public const string FILTER_EXPENSE = 'expense';
    public const string FILTER_INCOME = 'income';

    private const string DATA_YEAR = 'transactionListYear';
    private const string DATA_MONTH = 'transactionListMonth';
    private const string DATA_FILTER = 'transactionListFilter';
    private const string DATA_EDIT_ID = 'transactionEditId';

    public static function register(Nutgram $bot): void
    {
        $bot->onCallbackQueryData(
            self::pattern(self::LIST, 'year', 'month', 'filter'),
            [TransactionsListHandler::class, 'list'],
        );
        $bot->onCallbackQueryData(
            self::pattern(self::MONTH, 'year', 'month', 'filter', 'page'),
            [TransactionsListHandler::class, 'month'],
        );
        $bot->onCallbackQueryData(
            self::pattern(self::VIEW, 'id'),
            [TransactionViewHandler::class, '__invoke'],
        );
        $bot->onCallbackQueryData(
            self::pattern(self::MONEY, 'id'),
            [TransactionEditHandler::class, 'money'],
        );
        $bot->onCallbackQueryData(
            self::pattern(self::DATE, 'id'),
            [TransactionEditHandler::class, 'date'],
        );
        $bot->onCallbackQueryData(
            self::pattern(self::DESCRIPTION, 'id'),
            [TransactionEditHandler::class, 'description'],
        );
        $bot->onCallbackQueryData(
            self::pattern(self::CATEGORY, 'id'),
            [TransactionViewHandler::class, 'categories'],
        );
        $bot->onCallbackQueryData(
            self::pattern(self::SET_CATEGORY, 'categoryId'),
            [TransactionViewHandler::class, 'setCategory'],
        );
        $bot->onCallbackQueryData(
            self::pattern(self::DELETE, 'id'),
            [TransactionDeleteHandler::class, 'confirm'],
        );
        $bot->onCallbackQueryData(
            self::pattern(self::DELETE_OK, 'id'),
            [TransactionDeleteHandler::class, 'delete'],
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

    /**
     * @throws InvalidArgumentException
     */
    public static function rememberList(Nutgram $bot, string $year, string $month, string $filter): void
    {
        $bot->setUserData(self::DATA_YEAR, $year);
        $bot->setUserData(self::DATA_MONTH, $month);
        $bot->setUserData(self::DATA_FILTER, $filter);
    }

    /**
     * @return array{year: string, month: string, filter: string}
     * @throws InvalidArgumentException
     */
    public static function listContext(Nutgram $bot): array
    {
        $year = $bot->getUserData(self::DATA_YEAR);
        $month = $bot->getUserData(self::DATA_MONTH);
        $filter = $bot->getUserData(self::DATA_FILTER);

        return [
            'year' => is_string($year) && $year !== '' ? $year : date('Y'),
            'month' => is_string($month) && $month !== '' ? $month : date('n'),
            'filter' => is_string($filter) && $filter !== '' ? $filter : self::FILTER_ALL,
        ];
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function rememberEditId(Nutgram $bot, string $transactionId): void
    {
        $bot->setUserData(self::DATA_EDIT_ID, $transactionId);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function editId(Nutgram $bot): ?string
    {
        $id = $bot->getUserData(self::DATA_EDIT_ID);

        return is_string($id) && $id !== '' ? $id : null;
    }
}
