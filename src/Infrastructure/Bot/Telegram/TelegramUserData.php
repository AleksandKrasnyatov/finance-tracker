<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram;

use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\TelegramId;
use DomainException;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Nutgram;
use UnexpectedValueException;

final readonly class TelegramUserData
{
    public const string KEY_USER_ID = 'userId';
    public const string KEY_ACCOUNT_ID = 'currentAccountId';

    public function __construct(
        private UserRepositoryInterface $users,
    ) {
    }

    public function remember(Nutgram $bot, string $userId, string $accountId, ?int $telegramId = null): void
    {
        $bot->setUserData(self::KEY_USER_ID, $userId, $telegramId);
        $bot->setUserData(self::KEY_ACCOUNT_ID, $accountId, $telegramId);
    }

    /**
     * @return array{userId: string, accountId: string}
     * @throws InvalidArgumentException
     */
    public function getOrSet(Nutgram $bot): array
    {
        $userId = $bot->getUserData(self::KEY_USER_ID);
        $accountId = $bot->getUserData(self::KEY_ACCOUNT_ID);

        if (is_string($userId) && $userId !== '' && is_string($accountId) && $accountId !== '') {
            return [
                'userId' => $userId,
                'accountId' => $accountId,
            ];
        }

        return $this->refresh($bot);
    }

    /**
     * @return array{userId: string, accountId: string}
     */
    public function refresh(Nutgram $bot): array
    {
        $telegramId = $bot->userId();
        if ($telegramId === null) {
            throw new UnexpectedValueException('Telegram user is missing from the update.');
        }

        $user = $this->users->getByTelegramId(new TelegramId($telegramId));
        $account = $user->getAccounts()[0] ?? throw new DomainException('Сначала выполните /start.');

        $this->remember($bot, $user->id->value, $account->id->value, $telegramId);

        return [
            'userId' => $user->id->value,
            'accountId' => $account->id->value,
        ];
    }
}
