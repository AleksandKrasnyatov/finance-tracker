<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Conversation;

use App\Application\Gateway\TranslatorInterface;
use App\Application\UseCase\Account\Category\CreateCategoryCommand;
use App\Application\UseCase\Account\Category\CreateCategoryHandler;
use App\Domain\Enum\Locale;
use App\Domain\Enum\TransactionType;
use App\Infrastructure\Bot\Telegram\TelegramUserData;
use DomainException;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use UnexpectedValueException;

final class AddCategoryConversation extends Conversation
{
    public ?TransactionType $type = null;

    public function __construct(
        private readonly CreateCategoryHandler $createCategory,
        private readonly TelegramUserData $userData,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function start(Nutgram $bot): void
    {
        [$userId, $chatId] = $this->resolveIds($bot);
        $locale = $this->locale($bot);

        $bot->sendMessage(
            text: $this->translator->trans('bot.category.askType', locale: $locale),
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make(
                        $this->translator->trans('bot.button.income', locale: $locale),
                        callback_data: TransactionType::Income->value,
                    ),
                    InlineKeyboardButton::make(
                        $this->translator->trans('bot.button.expense', locale: $locale),
                        callback_data: TransactionType::Expense->value,
                    ),
                )
                ->addRow(
                    InlineKeyboardButton::make(
                        $this->translator->trans('bot.button.cancel', locale: $locale),
                        callback_data: 'cancel',
                    ),
                ),
        );

        $this->step = 'askName';
        $bot->stepConversation($this, $userId, $chatId);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function askName(Nutgram $bot): void
    {
        if (!$bot->isCallbackQuery()) {
            $this->start($bot);
            return;
        }

        [$userId, $chatId] = $this->resolveIds($bot);
        $locale = $this->locale($bot);
        $data = (string)$bot->callbackQuery()?->data;
        $bot->answerCallbackQuery();

        if ($data === 'cancel') {
            $bot->sendMessage($this->translator->trans('bot.category.cancelled', locale: $locale));
            $bot->endConversation($userId, $chatId);
            $this->closing($bot);
            return;
        }

        if (!$type = TransactionType::tryFrom($data)) {
            $this->start($bot);
            return;
        }

        $this->type = $type;
        $bot->sendMessage($this->translator->trans('bot.category.enterName', locale: $locale));
        $this->step = 'create';
        $bot->stepConversation($this, $userId, $chatId);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function create(Nutgram $bot): void
    {
        [$userId, $chatId] = $this->resolveIds($bot);
        $locale = $this->locale($bot);
        $name = trim((string)$bot->message()?->text);
        if ($name === '' || str_starts_with($name, '/')) {
            $bot->sendMessage($this->translator->trans('bot.category.enterNameText', locale: $locale));
            $this->step = 'create';
            $bot->stepConversation($this, $userId, $chatId);
            return;
        }

        try {
            $context = $this->userData->getOrSet($bot);

            $this->createCategory->handle(new CreateCategoryCommand(
                $context['userId'],
                $context['accountId'],
                $this->type->value ?? '',
                $name,
            ));
        } catch (DomainException $exception) {
            $bot->sendMessage($exception->getMessage());
            $bot->endConversation($userId, $chatId);
            $this->closing($bot);
            return;
        }

        $typeLabel = $this->type === null
            ? ''
            : $this->translator->trans($this->type->value, locale: $locale);

        $bot->sendMessage($this->translator->trans(
            'bot.category.created',
            [
                '%name%' => $name,
                '%type%' => $typeLabel,
            ],
            $locale,
        ));
        $bot->endConversation($userId, $chatId);
        $this->closing($bot);
    }

    /**
     * @return array{int, int}
     */
    private function resolveIds(Nutgram $bot): array
    {
        $userId = $bot->userId();
        $chatId = $bot->chatId();
        if ($userId === null || $chatId === null) {
            throw new UnexpectedValueException('Telegram user is missing from the update.');
        }

        return [$userId, $chatId];
    }

    /**
     * @throws InvalidArgumentException
     */
    private function locale(Nutgram $bot): Locale
    {
        return $this->userData->getOrSet($bot)['locale'];
    }
}
