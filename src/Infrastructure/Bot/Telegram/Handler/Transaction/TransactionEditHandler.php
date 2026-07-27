<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler\Transaction;

use App\Infrastructure\Bot\Telegram\Conversation\ChangeTransactionDateConversation;
use App\Infrastructure\Bot\Telegram\Conversation\ChangeTransactionDescriptionConversation;
use App\Infrastructure\Bot\Telegram\Conversation\ChangeTransactionMoneyConversation;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Nutgram;

final readonly class TransactionEditHandler
{
    /**
     * @throws InvalidArgumentException
     */
    public function money(Nutgram $bot, string $id): void
    {
        $bot->answerCallbackQuery();
        ChangeTransactionMoneyConversation::begin($bot, data: [
            'transactionId' => $id,
        ]);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function date(Nutgram $bot, string $id): void
    {
        $bot->answerCallbackQuery();
        ChangeTransactionDateConversation::begin($bot, data: [
            'transactionId' => $id,
        ]);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function description(Nutgram $bot, string $id): void
    {
        $bot->answerCallbackQuery();
        ChangeTransactionDescriptionConversation::begin($bot, data: [
            'transactionId' => $id,
        ]);
    }
}
