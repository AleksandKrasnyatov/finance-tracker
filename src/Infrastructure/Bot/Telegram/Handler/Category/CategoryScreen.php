<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler\Category;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use UnexpectedValueException;

final class CategoryScreen
{
    public static function render(Nutgram $bot, string $text, InlineKeyboardMarkup $markup): void
    {
        if ($bot->isCallbackQuery()) {
            $bot->answerCallbackQuery();
            $bot->editMessageText(text: $text, reply_markup: $markup);

            return;
        }

        $bot->sendMessage(text: $text, reply_markup: $markup);
    }

    public static function ensureUser(Nutgram $bot): void
    {
        if ($bot->userId() === null) {
            throw new UnexpectedValueException('Telegram user is missing from the update.');
        }
    }
}
