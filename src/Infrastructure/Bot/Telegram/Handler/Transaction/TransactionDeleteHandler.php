<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler\Transaction;

use App\Application\Gateway\TranslatorInterface;
use App\Application\UseCase\Account\Command\Transaction\DeleteTransactionCommand;
use App\Application\UseCase\Account\Command\Transaction\DeleteTransactionHandler;
use App\Application\UseCase\Account\Query\GetAccountTransactionHandler;
use App\Application\UseCase\Account\Query\GetAccountTransactionQuery;
use App\Infrastructure\Bot\Telegram\TelegramScreen;
use App\Infrastructure\Bot\Telegram\TelegramUserData;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

final readonly class TransactionDeleteHandler
{
    public function __construct(
        private TelegramUserData $userData,
        private GetAccountTransactionHandler $getTransaction,
        private DeleteTransactionHandler $deleteTransaction,
        private TransactionsListHandler $list,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function confirm(Nutgram $bot, string $id): void
    {
        TelegramScreen::ensureUser($bot);
        $context = $this->userData->getOrSet($bot);
        $locale = $context['locale'];
        $transaction = $this->getTransaction->handle(new GetAccountTransactionQuery(
            $context['userId'],
            $context['accountId'],
            $id,
        ))->transaction;
        $amount = number_format((float)$transaction->amount, thousands_separator: ' ');

        TelegramScreen::render(
            $bot,
            $this->translator->trans('bot.transactions.deleteConfirm', [
                '%amount%' => $amount . ' ' . $transaction->currency->symbol(),
                '%category%' => $transaction->categoryName,
            ], $locale),
            InlineKeyboardMarkup::make()->addRow(
                InlineKeyboardButton::make(
                    $this->translator->trans('bot.transactions.deleteYes', locale: $locale),
                    callback_data: TransactionCallback::data(TransactionCallback::DELETE_OK, $id),
                ),
                InlineKeyboardButton::make(
                    $this->translator->trans('bot.transactions.deleteNo', locale: $locale),
                    callback_data: TransactionCallback::data(TransactionCallback::VIEW, $id),
                ),
            ),
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function delete(Nutgram $bot, string $id): void
    {
        TelegramScreen::ensureUser($bot);
        $context = $this->userData->getOrSet($bot);

        $this->deleteTransaction->handle(new DeleteTransactionCommand(
            $context['userId'],
            $context['accountId'],
            $id,
        ));

        $list = TransactionCallback::listContext($bot);
        $this->list->list($bot, $list['year'], $list['month'], $list['filter']);
    }
}
