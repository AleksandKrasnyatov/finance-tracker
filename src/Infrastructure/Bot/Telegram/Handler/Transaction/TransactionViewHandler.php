<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler\Transaction;

use App\Application\Fetcher\Account\AccountTransaction;
use App\Application\Gateway\TranslatorInterface;
use App\Application\UseCase\Account\Command\Transaction\ChangeTransactionCategoryCommand;
use App\Application\UseCase\Account\Command\Transaction\ChangeTransactionCategoryHandler;
use App\Application\UseCase\Account\Query\GetAccountCategoriesHandler;
use App\Application\UseCase\Account\Query\GetAccountCategoriesQuery;
use App\Application\UseCase\Account\Query\GetAccountTransactionHandler;
use App\Application\UseCase\Account\Query\GetAccountTransactionQuery;
use App\Domain\Enum\Locale;
use App\Domain\Enum\TransactionType;
use App\Infrastructure\Bot\Telegram\TelegramScreen;
use App\Infrastructure\Bot\Telegram\TelegramUserData;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use UnexpectedValueException;

final readonly class TransactionViewHandler
{
    public function __construct(
        private TelegramUserData $userData,
        private GetAccountTransactionHandler $getTransaction,
        private GetAccountCategoriesHandler $categories,
        private ChangeTransactionCategoryHandler $changeCategory,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function __invoke(Nutgram $bot, string $id): void
    {
        TelegramScreen::ensureUser($bot);
        $context = $this->userData->getOrSet($bot);
        $locale = $context['locale'];
        $transaction = $this->transaction($context['userId'], $context['accountId'], $id);

        TelegramScreen::render(
            $bot,
            $this->translator->trans('bot.transactions.detail', locale: $locale),
            $this->editingMarkup($transaction, $locale),
            ParseMode::HTML,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function categories(Nutgram $bot, string $id): void
    {
        TelegramScreen::ensureUser($bot);
        $context = $this->userData->getOrSet($bot);
        $locale = $context['locale'];
        $transaction = $this->transaction($context['userId'], $context['accountId'], $id);
        TransactionCallback::rememberEditId($bot, $id);

        $result = $this->categories->handle(new GetAccountCategoriesQuery(
            $context['userId'],
            $context['accountId'],
        ));
        $items = $transaction->type === TransactionType::Income ? $result->incomes : $result->expenses;

        //todo тоже самое что в категориях, надо вынести
        $markup = InlineKeyboardMarkup::make();
        foreach ($items as $category) {
            $prefix = $category->id->value === $transaction->categoryId ? '✓ ' : '';
            $markup->addRow(InlineKeyboardButton::make(
                $prefix . $category->name,
                callback_data: TransactionCallback::data(
                    TransactionCallback::SET_CATEGORY,
                    $category->id->value,
                ),
            ));
        }
        $markup->addRow(InlineKeyboardButton::make(
            $this->translator->trans('bot.transactions.back', locale: $locale),
            callback_data: TransactionCallback::data(TransactionCallback::VIEW, $id),
        ));

        TelegramScreen::render(
            $bot,
            $this->translator->trans('bot.transactions.chooseCategory', locale: $locale),
            $markup,
            ParseMode::HTML,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setCategory(Nutgram $bot, string $categoryId): void
    {
        //todo просится в edit handler
        TelegramScreen::ensureUser($bot);
        $context = $this->userData->getOrSet($bot);
        $transactionId = TransactionCallback::editId($bot)
            ?? throw new UnexpectedValueException('Transaction edit context is missing.');

        $this->changeCategory->handle(new ChangeTransactionCategoryCommand(
            $context['userId'],
            $context['accountId'],
            $transactionId,
            $categoryId,
        ));

        $locale = $context['locale'];
        $transaction = $this->transaction($context['userId'], $context['accountId'], $transactionId);
        $bot->answerCallbackQuery(
            text: $this->translator->trans('bot.transactions.categoryChanged', locale: $locale),
        );
        $bot->editMessageText(
            text: $this->translator->trans('bot.transactions.detail', locale: $locale),
            parse_mode: ParseMode::HTML,
            reply_markup: $this->editingMarkup($transaction, $locale),
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function transaction(string $userId, string $accountId, string $id): AccountTransaction
    {
        return $this->getTransaction->handle(new GetAccountTransactionQuery(
            $userId,
            $accountId,
            $id,
        ))->transaction;
    }

    private function editingMarkup(AccountTransaction $transaction, Locale $locale): InlineKeyboardMarkup
    {
        $amount = number_format((float)$transaction->amount, thousands_separator: ' ');

        return InlineKeyboardMarkup::make()
            ->addRow(InlineKeyboardButton::make(
                sprintf('%s %s', $amount, $transaction->currency->symbol()),
                callback_data: TransactionCallback::data(TransactionCallback::MONEY, $transaction->id),
            ))
            ->addRow(InlineKeyboardButton::make(
                $transaction->categoryName,
                callback_data: TransactionCallback::data(TransactionCallback::CATEGORY, $transaction->id),
            ))
            ->addRow(InlineKeyboardButton::make(
                $transaction->date->format('Y-m-d'),
                callback_data: TransactionCallback::data(TransactionCallback::DATE, $transaction->id),
            ))
            ->addRow(InlineKeyboardButton::make(
                $transaction->description ?: $this->translator->trans('bot.transactions.descriptionPlaceholder', locale: $locale),
                callback_data: TransactionCallback::data(TransactionCallback::DESCRIPTION, $transaction->id),
            ))
            ->addRow(InlineKeyboardButton::make(
                $this->translator->trans('bot.transactions.delete', locale: $locale),
                callback_data: TransactionCallback::data(TransactionCallback::DELETE, $transaction->id),
            ))
            ->addRow(InlineKeyboardButton::make(
                $this->translator->trans('bot.transactions.back', locale: $locale),
                callback_data: TransactionCallback::BACK,
            ));
    }
}
