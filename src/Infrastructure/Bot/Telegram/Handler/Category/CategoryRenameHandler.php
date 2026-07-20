<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler\Category;

use App\Infrastructure\Bot\Telegram\Conversation\RenameCategoryConversation;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Nutgram;

final readonly class CategoryRenameHandler
{
    /**
     * @throws InvalidArgumentException
     */
    public function __invoke(Nutgram $bot, string $id, string $type): void
    {
        CategoryScreen::ensureUser($bot);
        $bot->answerCallbackQuery();
        RenameCategoryConversation::begin($bot, data: [
            'categoryId' => $id,
            'type' => $type,
        ]);
    }
}
