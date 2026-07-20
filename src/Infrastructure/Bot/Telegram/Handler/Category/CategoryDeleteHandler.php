<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler\Category;

use App\Application\Gateway\TranslatorInterface;
use App\Application\UseCase\Account\Command\Category\DeleteCategoryCommand;
use App\Application\UseCase\Account\Command\Category\DeleteCategoryHandler;
use App\Application\UseCase\Account\Query\GetAccountCategoryHandler;
use App\Application\UseCase\Account\Query\GetAccountCategoryQuery;
use App\Infrastructure\Bot\Telegram\TelegramUserData;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

final readonly class CategoryDeleteHandler
{
    public function __construct(
        private TelegramUserData $userData,
        private GetAccountCategoryHandler $getCategory,
        private DeleteCategoryHandler $deleteCategory,
        private CategoriesListHandler $list,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function confirm(Nutgram $bot, string $id, string $type): void
    {
        CategoryScreen::ensureUser($bot);
        $context = $this->userData->getOrSet($bot);
        $locale = $context['locale'];
        $category = $this->getCategory->handle(new GetAccountCategoryQuery(
            $context['userId'],
            $context['accountId'],
            $id,
        ))->category;

        CategoryScreen::render(
            $bot,
            $this->translator->trans('bot.categories.deleteConfirm', [
                '%name%' => $category->name,
            ], $locale),
            InlineKeyboardMarkup::make()->addRow(
                InlineKeyboardButton::make(
                    $this->translator->trans('bot.categories.deleteYes', locale: $locale),
                    callback_data: CategoryCallback::data(CategoryCallback::DELETE_OK, $id, $type),
                ),
                InlineKeyboardButton::make(
                    $this->translator->trans('bot.categories.deleteNo', locale: $locale),
                    callback_data: CategoryCallback::data(CategoryCallback::VIEW, $id),
                ),
            ),
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function delete(Nutgram $bot, string $id, string $type): void
    {
        CategoryScreen::ensureUser($bot);
        $context = $this->userData->getOrSet($bot);

        $this->deleteCategory->handle(new DeleteCategoryCommand(
            $context['userId'],
            $context['accountId'],
            $id,
        ));

        $this->list->byType($bot, $type);
    }
}
