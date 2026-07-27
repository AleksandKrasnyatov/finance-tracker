<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

final class TelegramScreen
{
    public static function render(
        Nutgram $bot,
        string $text,
        InlineKeyboardMarkup $markup,
        ParseMode|string|null $parseMode = null,
    ): void {
        if ($bot->isCallbackQuery()) {
            $bot->answerCallbackQuery();
            $bot->editMessageText(text: $text, parse_mode: $parseMode, reply_markup: $markup);

            return;
        }

        $bot->sendMessage(text: $text, parse_mode: $parseMode, reply_markup: $markup);
    }
}
