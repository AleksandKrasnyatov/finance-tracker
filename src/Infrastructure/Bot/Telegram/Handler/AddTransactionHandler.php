<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler;

use App\Application\UseCase\Account\Transaction\AddTransactionCommand;
use App\Application\UseCase\Account\Transaction\AddTransactionHandler as Handler;
use App\Domain\Enum\TransactionType;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\TelegramId;
use DomainException;
use InvalidArgumentException;
use SergiX44\Nutgram\Nutgram;
use UnexpectedValueException;

final readonly class AddTransactionHandler
{
    public function __construct(
        private Handler $handler,
    ) {
    }

    public function __invoke(
        Nutgram $bot,
        string $sign,
        string $amount,
        string $category,
        ?string $description = null,
    ): void {
        $telegramId = $bot->userId();
        if ($telegramId === null) {
            throw new UnexpectedValueException('Telegram user is missing from the update.');
        }

        $type = $this->resolveType($sign);
        $amount = str_replace(',', '.', $amount);
        $comment = trim((string)$description);

        try {
            $user = $bot->getContainer()->get(UserRepositoryInterface::class)->getByTelegramId(new TelegramId($telegramId));
            $account = $user->getAccounts()[0]
                ?? throw new DomainException('Сначала выполните /start.');

            $this->handler->handle(new AddTransactionCommand(
                $user->id->value,
                $account->id->value,
                $type->value,
                $amount,
                $category,
                $comment,
            ));
        } catch (DomainException | InvalidArgumentException $exception) {
            $bot->sendMessage($this->userMessage($exception));
            return;
        }

        $categoryName = mb_strtolower($category);
        $message = "Записал {$sign}{$amount} в «{$categoryName}» ({$type->title()}).";
        if ($comment !== '') {
            $message .= " Комментарий: {$comment}";
        }

        $bot->sendMessage($message);
    }

    private function resolveType(string $sign): TransactionType
    {
        return match ($sign) {
            '+' => TransactionType::Income,
            '-' => TransactionType::Expense,
            default => throw new UnexpectedValueException('Transaction sign must be + or -.'),
        };
    }

    /**
     * todo нужно разобраться с обработкой исключений
     */
    private function userMessage(DomainException|InvalidArgumentException $exception): string
    {
        return match ($exception->getMessage()) {
            'Category is not found.' => 'Категория не найдена. Проверьте название и знак (+ доход / − расход) или добавьте её через «Добавить категорию».',
            'The user has no account.', 'Сначала выполните /start.' => 'Сначала выполните /start.',
            default => $exception->getMessage(),
        };
    }
}
