<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Conversation;

use App\Application\Gateway\TranslatorInterface;
use App\Application\UseCase\Account\Command\Category\ChangeCategoryNameCommand;
use App\Application\UseCase\Account\Command\Category\ChangeCategoryNameHandler;
use App\Domain\Enum\Locale;
use App\Infrastructure\Bot\Telegram\Handler\Category\CategoriesListHandler;
use App\Infrastructure\Bot\Telegram\TelegramUserData;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

final class RenameCategoryConversation extends Conversation
{
    public string $categoryId;
    public string $type;

    public function __construct(
        private readonly ChangeCategoryNameHandler $changeCategoryName,
        private readonly CategoriesListHandler $categories,
        private readonly TelegramUserData $userData,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function start(Nutgram $bot, string $categoryId, string $type): void
    {
        $this->categoryId = $categoryId;
        $this->type = $type;

        $bot->sendMessage($this->translator->trans(
            'bot.categories.enterNewName',
            locale: $this->locale($bot),
        ));
        $this->next('rename');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function rename(Nutgram $bot): void
    {
        $locale = $this->locale($bot);
        $name = trim((string)$bot->message()?->text);
        if ($name === '' || str_starts_with($name, '/')) {
            $bot->sendMessage($this->translator->trans('bot.category.enterNameText', locale: $locale));
            $this->next('rename');

            return;
        }

        $context = $this->userData->getOrSet($bot);
        $this->changeCategoryName->handle(new ChangeCategoryNameCommand(
            $context['userId'],
            $context['accountId'],
            $this->categoryId,
            $name,
        ));

        $bot->sendMessage($this->translator->trans(
            'bot.categories.renamed',
            ['%name%' => mb_strtolower($name)],
            $locale,
        ));
        $this->end();
        $this->categories->byType($bot, $this->type);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function locale(Nutgram $bot): Locale
    {
        return $this->userData->getOrSet($bot)['locale'];
    }
}
