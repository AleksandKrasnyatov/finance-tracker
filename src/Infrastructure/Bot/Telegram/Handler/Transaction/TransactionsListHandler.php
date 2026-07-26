<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler\Transaction;

use App\Application\Fetcher\Account\AccountTransaction;
use App\Application\Fetcher\Account\AccountTransactionMonth;
use App\Application\Gateway\TranslatorInterface;
use App\Application\UseCase\Account\Query\GetAccountTransactionMonthsHandler;
use App\Application\UseCase\Account\Query\GetAccountTransactionMonthsQuery;
use App\Application\UseCase\Account\Query\GetAccountTransactionsHandler;
use App\Application\UseCase\Account\Query\GetAccountTransactionsQuery;
use App\Domain\Enum\Locale;
use App\Infrastructure\Bot\Telegram\TelegramScreen;
use App\Infrastructure\Bot\Telegram\TelegramUserData;
use DateTimeImmutable;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

final readonly class TransactionsListHandler
{
    public function __construct(
        private TelegramUserData $userData,
        private GetAccountTransactionsHandler $transactions,
        private GetAccountTransactionMonthsHandler $months,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function __invoke(Nutgram $bot): void
    {
        $now = new DateTimeImmutable();
        $this->list(
            $bot,
            $now->format('Y'),
            $now->format('n'),
            TransactionCallback::FILTER_ALL,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function list(Nutgram $bot, string $year, string $month, string $filter): void
    {
        TelegramScreen::ensureUser($bot);
        $context = $this->userData->getOrSet($bot);
        $locale = $context['locale'];
        TransactionCallback::rememberList($bot, $year, $month, $filter);

        $result = $this->transactions->handle(new GetAccountTransactionsQuery(
            $context['userId'],
            $context['accountId'],
            $year,
            $month,
            $filter === TransactionCallback::FILTER_ALL ? null : $filter,
        ));

        TelegramScreen::render(
            $bot,
            $this->translator->trans('bot.transactions.title', locale: $locale),
            $this->listMarkup($year, $month, $filter, $result->transactions, $locale),
            ParseMode::HTML,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function month(Nutgram $bot, string $year, string $month, string $filter, string $page): void
    {
        TelegramScreen::ensureUser($bot);
        $context = $this->userData->getOrSet($bot);
        $locale = $context['locale'];
        $pageNumber = max(1, (int)$page);

        $result = $this->months->handle(new GetAccountTransactionMonthsQuery(
            $context['userId'],
            $context['accountId'],
            $pageNumber,
        ));

        TelegramScreen::render(
            $bot,
            $this->translator->trans('bot.transactions.chooseMonth', locale: $locale),
            $this->monthMarkup(
                $year,
                $month,
                $filter,
                $result->page,
                $result->months,
                $result->hasPrev,
                $result->hasNext,
                $locale
            ),
            ParseMode::HTML,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function back(Nutgram $bot): void
    {
        $list = TransactionCallback::listContext($bot);
        $this->list($bot, $list['year'], $list['month'], $list['filter']);
    }

    /**
     * @param list<AccountTransaction> $transactions
     */
    private function listMarkup(
        string $year,
        string $month,
        string $filter,
        array $transactions,
        Locale $locale,
    ): InlineKeyboardMarkup {
        $markup = InlineKeyboardMarkup::make()
            ->addRow(InlineKeyboardButton::make(
                $this->periodLabel($year, $month, $locale),
                callback_data: TransactionCallback::data(
                    TransactionCallback::MONTH,
                    $year,
                    $month,
                    $filter,
                    '1',
                ),
            ))
            ->addRow(
                $this->filterButton(
                    TransactionCallback::FILTER_ALL,
                    $filter,
                    'bot.transactions.filter.all',
                    $year,
                    $month,
                    $locale,
                ),
                $this->filterButton(
                    TransactionCallback::FILTER_EXPENSE,
                    $filter,
                    'bot.transactions.filter.expense',
                    $year,
                    $month,
                    $locale,
                ),
                $this->filterButton(
                    TransactionCallback::FILTER_INCOME,
                    $filter,
                    'bot.transactions.filter.income',
                    $year,
                    $month,
                    $locale,
                ),
            );

        foreach ($transactions as $transaction) {
            $markup->addRow(InlineKeyboardButton::make(
                $this->transactionLabel($transaction),
                callback_data: TransactionCallback::data(
                    TransactionCallback::VIEW,
                    $transaction->id,
                ),
            ));
        }

        return $markup->addRow(InlineKeyboardButton::make(
            $this->translator->trans('bot.transactions.back', locale: $locale),
            callback_data: TransactionCallback::BACK,
        ));
    }

    /**
     * @param list<AccountTransactionMonth> $months
     */
    private function monthMarkup(
        string $year,
        string $month,
        string $filter,
        int $page,
        array $months,
        bool $hasPrev,
        bool $hasNext,
        Locale $locale,
    ): InlineKeyboardMarkup {
        $markup = InlineKeyboardMarkup::make();

        foreach ($months as $item) {
            $markup->addRow(InlineKeyboardButton::make(
                sprintf(
                    '%s %d',
                    $this->translator->trans('bot.month.' . $item->month, locale: $locale),
                    $item->year,
                ),
                callback_data: TransactionCallback::data(
                    TransactionCallback::LIST,
                    $item->year,
                    $item->month,
                    $filter,
                ),
            ));
        }

        if ($hasPrev) {
            $markup->addRow(InlineKeyboardButton::make(
                '‹',
                callback_data: TransactionCallback::data(
                    TransactionCallback::MONTH,
                    $year,
                    $month,
                    $filter,
                    (string)($page - 1),
                ),
            ));
        }

        if ($hasNext) {
            $markup->addRow(InlineKeyboardButton::make(
                '›',
                callback_data: TransactionCallback::data(
                    TransactionCallback::MONTH,
                    $year,
                    $month,
                    $filter,
                    (string)($page + 1),
                ),
            ));
        }

        return $markup->addRow(InlineKeyboardButton::make(
            $this->translator->trans('bot.transactions.back', locale: $locale),
            callback_data: TransactionCallback::data(
                TransactionCallback::LIST,
                $year,
                $month,
                $filter,
            ),
        ));
    }

    private function periodLabel(string $year, string $month, Locale $locale): string
    {
        $label = $this->translator->trans('bot.month.' . $month, locale: $locale);
        if ($year === new DateTimeImmutable()->format('Y')) {
            return $label;
        }

        return sprintf('%s %s', $label, $year);
    }

    private function filterButton(
        string $value,
        string $current,
        string $labelKey,
        string $year,
        string $month,
        Locale $locale,
    ): InlineKeyboardButton {
        $emoji = $value === $current ? '🟠' : '⚪️';

        return InlineKeyboardButton::make(
            $emoji . ' ' . $this->translator->trans($labelKey, locale: $locale),
            callback_data: TransactionCallback::data(
                TransactionCallback::LIST,
                $year,
                $month,
                $value,
            ),
        );
    }

    private function transactionLabel(AccountTransaction $transaction): string
    {
        $amount = number_format((float)$transaction->amount, thousands_separator: ' ');

        return sprintf(
            '%s %s %s 🗓%s',
            $transaction->type->sign(),
            $amount,
            $transaction->categoryName,
            $transaction->date->format('d'),
        );
    }
}
