<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Conversation;

use App\Application\UseCase\Account\Category\CreateCategoryCommand;
use App\Application\UseCase\Account\Category\CreateCategoryHandler;
use App\Domain\Enum\TransactionType;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\TelegramId;
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
     */
    public function start(Nutgram $bot): void
    {
        [$userId, $chatId] = $this->resolveIds($bot);

        $bot->sendMessage(
            text: 'Какой тип категории?',
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('Доход', callback_data: TransactionType::Income->value),
                    InlineKeyboardButton::make('Расход', callback_data: TransactionType::Expense->value),
                )
                ->addRow(
                    InlineKeyboardButton::make('Отмена', callback_data: 'cancel'),
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
        $data = (string)$bot->callbackQuery()?->data;
        $bot->answerCallbackQuery();

        if ($data === 'cancel') {
            $bot->sendMessage('Добавление категории отменено.');
            $bot->endConversation($userId, $chatId);
            $this->closing($bot);
            return;
        }

        if (!$type = TransactionType::tryFrom($data)) {
            $this->start($bot);
            return;
        }

        $this->type = $type;
        $bot->sendMessage('Введите название категории:');
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
        $name = trim((string)$bot->message()?->text);
        if ($name === '' || str_starts_with($name, '/')) {
            $bot->sendMessage('Введите название категории текстом:');
            $this->step = 'create';
            $bot->stepConversation($this, $userId, $chatId);
            return;
        }

        try {
            $user = $bot->getContainer()
                ->get(UserRepositoryInterface::class)
                ->getByTelegramId(new TelegramId($userId));

            $account = $user->getAccounts()[0]
                ?? throw new DomainException('Сначала выполните /start.');

            $bot->getContainer()
                ->get(CreateCategoryHandler::class)
                ->handle(new CreateCategoryCommand(
                    $user->id->value,
                    $account->id->value,
                    $this->type->value ?? '',
                    $name,
                ));
        } catch (DomainException $exception) {
            $bot->sendMessage($exception->getMessage());
            $bot->endConversation($userId, $chatId);
            $this->closing($bot);
            return;
        }

        $typeLabel = $this->type?->title();
        $bot->sendMessage("Категория «{$name}» ({$typeLabel}) добавлена.");
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
}
