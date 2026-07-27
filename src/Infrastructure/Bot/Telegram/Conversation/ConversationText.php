<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Conversation;

use SergiX44\Nutgram\Nutgram;

final class ConversationText
{
    public static function fromMessage(Nutgram $bot): string
    {
        return trim((string) $bot->message()?->text);
    }

    public static function isCommandOrEmpty(string $text): bool
    {
        return $text === '' || self::isCommand($text);
    }

    public static function isCommand(string $text): bool
    {
        return str_starts_with($text, '/');
    }
}
