<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler\Category;

use App\Infrastructure\Bot\Telegram\Conversation\AddCategoryConversation;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Nutgram;

final readonly class CategoryAddHandler
{
    /**
     * @throws InvalidArgumentException
     */
    public function __invoke(Nutgram $bot, string $type): void
    {
        CategoryScreen::ensureUser($bot);
        if ($bot->isCallbackQuery()) {
            $bot->answerCallbackQuery();
        }

        AddCategoryConversation::begin($bot, data: ['type' => $type]);
    }
}
