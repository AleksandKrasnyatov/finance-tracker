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
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use UnexpectedValueException;

final class AddCategoryConversation extends Conversation
{
    public ?TransactionType $type = null;

    /**
     * @throws InvalidArgumentException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function start(Nutgram $bot): void
    {
        [$userId, $chatId] = $this->resolveIds($bot);
        $translator = $this->translator($bot);
        $locale = $this->locale($bot);

        $bot->sendMessage(
            text: $translator->trans('bot.category.ask_type', locale: $locale),
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make(
                        $translator->trans('bot.button.income', locale: $locale),
                        callback_data: TransactionType::Income->value,
                    ),
                    InlineKeyboardButton::make(
                        $translator->trans('bot.button.expense', locale: $locale),
                        callback_data: TransactionType::Expense->value,
                    ),
                )
                ->addRow(
                    InlineKeyboardButton::make(
                        $translator->trans('bot.button.cancel', locale: $locale),
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
        $translator = $this->translator($bot);
        $locale = $this->locale($bot);
        $data = (string)$bot->callbackQuery()?->data;
        $bot->answerCallbackQuery();

        if ($data === 'cancel') {
            $bot->sendMessage($translator->trans('bot.category.cancelled', locale: $locale));
            $bot->endConversation($userId, $chatId);
            $this->closing($bot);
            return;
        }

        if (!$type = TransactionType::tryFrom($data)) {
            $this->start($bot);
            return;
        }

        $this->type = $type;
        $bot->sendMessage($translator->trans('bot.category.enter_name', locale: $locale));
        $this->step = 'create';
        $bot->stepConversation($this, $userId, $chatId);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     */
    public function create(Nutgram $bot): void
    {
        [$userId, $chatId] = $this->resolveIds($bot);
        $translator = $this->translator($bot);
        $locale = $this->locale($bot);
        $name = trim((string)$bot->message()?->text);
        if ($name === '' || str_starts_with($name, '/')) {
            $bot->sendMessage($translator->trans('bot.category.enter_name_text', locale: $locale));
            $this->step = 'create';
            $bot->stepConversation($this, $userId, $chatId);
            return;
        }

        try {
            $context = $bot->getContainer()->get(TelegramUserData::class)->getOrSet($bot);

            $bot->getContainer()
                ->get(CreateCategoryHandler::class)
                ->handle(new CreateCategoryCommand(
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
            : $translator->trans($this->type->value, locale: $locale);

        $bot->sendMessage($translator->trans(
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function translator(Nutgram $bot): TranslatorInterface
    {
        return $bot->getContainer()->get(TranslatorInterface::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException
     */
    private function locale(Nutgram $bot): Locale
    {
        return $bot->getContainer()->get(TelegramUserData::class)->getOrSet($bot)['locale'];
    }
}
