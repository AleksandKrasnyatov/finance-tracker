<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler\Category;

use App\Application\Gateway\TranslatorInterface;
use App\Application\UseCase\Account\Query\GetAccountCategoryHandler;
use App\Application\UseCase\Account\Query\GetAccountCategoryQuery;
use App\Infrastructure\Bot\Telegram\TelegramUserData;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

final readonly class CategoryViewHandler
{
    public function __construct(
        private TelegramUserData $userData,
        private GetAccountCategoryHandler $getCategory,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function __invoke(Nutgram $bot, string $id): void
    {
        CategoryScreen::ensureUser($bot);
        $context = $this->userData->getOrSet($bot);
        $locale = $context['locale'];
        $category = $this->getCategory->handle(new GetAccountCategoryQuery(
            $context['userId'],
            $context['accountId'],
            $id,
        ))->category;
        $type = $category->type->value;

        $markup = InlineKeyboardMarkup::make()
            ->addRow(InlineKeyboardButton::make(
                $this->translator->trans('bot.categories.rename', locale: $locale),
                callback_data: CategoryCallback::data(CategoryCallback::RENAME, $id, $type),
            ))
            ->addRow(InlineKeyboardButton::make(
                $this->translator->trans('bot.categories.delete', locale: $locale),
                callback_data: CategoryCallback::data(CategoryCallback::DELETE, $id, $type),
            ))
            ->addRow(InlineKeyboardButton::make(
                $this->translator->trans('bot.categories.back', locale: $locale),
                callback_data: CategoryCallback::data(CategoryCallback::TYPE, $type),
            ));

        CategoryScreen::render(
            $bot,
            $this->translator->trans('bot.categories.detail', [
                '%name%' => $category->name,
                '%type%' => $this->translator->trans($type, locale: $locale),
            ], $locale),
            $markup,
        );
    }
}
