<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram;

use SergiX44\Nutgram\Nutgram;
use UnexpectedValueException;

//todo не очень понятно что за класс, цепляет глаз
final class TelegramUserGuard
{
    public static function telegramId(Nutgram $bot): int
    {
        $telegramId = $bot->userId();

        if ($telegramId === null) {
            throw new UnexpectedValueException('Telegram user is missing from the update.');
        }

        return $telegramId;
    }
}
