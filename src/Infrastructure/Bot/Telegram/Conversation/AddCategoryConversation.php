<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Conversation;

use App\Application\Gateway\TranslatorInterface;
use App\Application\UseCase\Account\Command\Category\CreateCategoryCommand;
use App\Application\UseCase\Account\Command\Category\CreateCategoryHandler;
use App\Domain\Enum\Locale;
use App\Domain\Enum\TransactionType;
use App\Infrastructure\Bot\Telegram\Handler\Category\CategoriesListHandler;
use App\Infrastructure\Bot\Telegram\TelegramUserData;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

final class AddCategoryConversation extends Conversation
{
    public TransactionType $type;

    public function __construct(
        private readonly CreateCategoryHandler $createCategory,
        private readonly CategoriesListHandler $categories,
        private readonly TelegramUserData $userData,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function start(Nutgram $bot, string $type): void
    {
        $this->type = TransactionType::fromName($type);
        $locale = $this->locale($bot);

        $bot->sendMessage($this->translator->trans('bot.category.enterName', locale: $locale));
        $this->next('create');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function create(Nutgram $bot): void
    {
        $locale = $this->locale($bot);
        $name = trim((string)$bot->message()?->text);
        if ($name === '' || str_starts_with($name, '/')) {
            $bot->sendMessage($this->translator->trans('bot.category.enterNameText', locale: $locale));
            $this->next('create');

            return;
        }

        $context = $this->userData->getOrSet($bot);
        $this->createCategory->handle(new CreateCategoryCommand(
            $context['userId'],
            $context['accountId'],
            $this->type->value,
            $name,
        ));

        $bot->sendMessage($this->translator->trans(
            'bot.category.created',
            [
                '%name%' => $name,
                '%type%' => $this->translator->trans($this->type->value, locale: $locale),
            ],
            $locale,
        ));
        $this->end();
        $this->categories->byType($bot, $this->type->value);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function locale(Nutgram $bot): Locale
    {
        return $this->userData->getOrSet($bot)['locale'];
    }
}
