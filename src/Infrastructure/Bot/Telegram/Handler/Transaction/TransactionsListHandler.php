<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler\Transaction;

use App\Application\Gateway\TranslatorInterface;
use App\Domain\Enum\Locale;
use App\Domain\Enum\TransactionType;
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

        // TODO Application: GetAccountTransactionsHandler::handle(
        //   new GetAccountTransactionsQuery(
        //     userId: $context['userId'],
        //     accountId: $context['accountId'],
        //     year: (int)$year,
        //     month: (int)$month,
        //     filter: $filter, // all|expense|income
        //   )
        // )
        // Expected item fields: id, type (TransactionType), amount (decimal string), categoryName, date (DateTimeImmutable)
        $transactions = [];

        TelegramScreen::render(
            $bot,
            $this->translator->trans('bot.transactions.title', locale: $locale),
            $this->markup($year, $month, $filter, $transactions, $locale),
            ParseMode::HTML,
        );
    }

    /**
     * Month picker — not implemented yet.
     */
    public function month(Nutgram $bot, string $year, string $month): void
    {
        TelegramScreen::ensureUser($bot);
        $bot->answerCallbackQuery();
        // TODO: month navigation / picker screen
    }

    /**
     * Transaction detail — not implemented yet.
     */
    public function view(Nutgram $bot, string $id): void
    {
        TelegramScreen::ensureUser($bot);
        $bot->answerCallbackQuery();
        // TODO Application + UI: GetAccountTransaction + edit/delete screen
    }

    /**
     * Parent navigation — destination TBD.
     */
    public function back(Nutgram $bot): void
    {
        TelegramScreen::ensureUser($bot);
        $bot->answerCallbackQuery();
        // TODO: navigate to parent screen when menu hierarchy exists
    }

    /**
     * @param list<array{
     *     id: string,
     *     type: TransactionType,
     *     amount: string,
     *     categoryName: string,
     *     date: DateTimeImmutable
     * }> $transactions
     */
    private function markup(
        string $year,
        string $month,
        string $filter,
        array $transactions,
        Locale $locale,
    ): InlineKeyboardMarkup {
        $markup = InlineKeyboardMarkup::make()
            ->addRow(InlineKeyboardButton::make(
                $this->translator->trans('bot.month.' . $month, locale: $locale),
                callback_data: TransactionCallback::data(
                    TransactionCallback::MONTH,
                    $year,
                    $month,
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
                    $transaction['id'],
                ),
            ));
        }

        return $markup->addRow(InlineKeyboardButton::make(
            $this->translator->trans('bot.transactions.back', locale: $locale),
            callback_data: TransactionCallback::BACK,
        ));
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

    /**
     * @param array{
     *     id: string,
     *     type: TransactionType,
     *     amount: string,
     *     categoryName: string,
     *     date: DateTimeImmutable
     * } $transaction
     */
    private function transactionLabel(array $transaction): string
    {
        $sign = $transaction['type'] === TransactionType::Income ? '+' : '-';
        $amount = number_format((float)$transaction['amount'], thousands_separator: ' ');
        $day = $transaction['date']->format('d');

        return sprintf('%s %s %s 🗓%s', $sign, $amount, $transaction['categoryName'], $day);
    }
}
