<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler\Category;

use App\Application\Gateway\TranslatorInterface;
use App\Application\UseCase\Account\Query\GetAccountCategoriesHandler;
use App\Application\UseCase\Account\Query\GetAccountCategoriesQuery;
use App\Domain\Enum\TransactionType;
use App\Infrastructure\Bot\Telegram\TelegramUserData;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

final readonly class CategoriesListHandler
{
    public function __construct(
        private TelegramUserData $userData,
        private GetAccountCategoriesHandler $categories,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function __invoke(Nutgram $bot): void
    {
        $this->list($bot);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function list(Nutgram $bot): void
    {
        CategoryScreen::ensureUser($bot);
        $locale = $this->userData->getOrSet($bot)['locale'];
        $markup = InlineKeyboardMarkup::make()
            ->addRow(InlineKeyboardButton::make(
                $this->translator->trans('bot.button.income', locale: $locale),
                callback_data: CategoryCallback::data(CategoryCallback::TYPE, TransactionType::Income->value),
            ))
            ->addRow(InlineKeyboardButton::make(
                $this->translator->trans('bot.button.expense', locale: $locale),
                callback_data: CategoryCallback::data(CategoryCallback::TYPE, TransactionType::Expense->value),
            ));

        CategoryScreen::render(
            $bot,
            $this->translator->trans('bot.categories.title', locale: $locale),
            $markup,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function byType(Nutgram $bot, string $type): void
    {
        CategoryScreen::ensureUser($bot);
        $transactionType = TransactionType::fromName($type);
        $context = $this->userData->getOrSet($bot);
        $locale = $context['locale'];
        $result = $this->categories->handle(new GetAccountCategoriesQuery(
            $context['userId'],
            $context['accountId'],
        ));
        $items = $transactionType === TransactionType::Income ? $result->incomes : $result->expenses;

        $markup = InlineKeyboardMarkup::make()
            ->addRow(InlineKeyboardButton::make(
                $this->translator->trans('bot.categories.add', locale: $locale),
                callback_data: CategoryCallback::data(CategoryCallback::ADD, $transactionType->value),
            ));

        foreach ($items as $category) {
            $markup->addRow(InlineKeyboardButton::make(
                $category->name,
                callback_data: CategoryCallback::data(CategoryCallback::VIEW, $category->id->value),
            ));
        }

        $markup->addRow(InlineKeyboardButton::make(
            $this->translator->trans('bot.categories.back', locale: $locale),
            callback_data: CategoryCallback::LIST,
        ));

        CategoryScreen::render(
            $bot,
            $this->translator->trans('bot.categories.typeTitle', [
                '%type%' => $this->translator->trans($transactionType->value, locale: $locale),
            ], $locale),
            $markup,
        );
    }
}
