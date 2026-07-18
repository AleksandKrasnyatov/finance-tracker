<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram;

use App\Domain\Enum\Locale;
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
    public const string KEY_LOCALE = 'locale';

    public function __construct(
        private UserRepositoryInterface $users,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function remember(
        Nutgram $bot,
        string $userId,
        string $accountId,
        Locale $locale,
        ?int $telegramId = null,
    ): void {
        $bot->setUserData(self::KEY_USER_ID, $userId, $telegramId);
        $bot->setUserData(self::KEY_ACCOUNT_ID, $accountId, $telegramId);
        $bot->setUserData(self::KEY_LOCALE, $locale, $telegramId);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function clear(Nutgram $bot): void
    {
        $telegramId = $bot->userId();

        $bot->deleteUserData(self::KEY_USER_ID, $telegramId);
        $bot->deleteUserData(self::KEY_ACCOUNT_ID, $telegramId);
        $bot->deleteUserData(self::KEY_LOCALE, $telegramId);
    }

    /**
     * @return array{userId: string, accountId: string, locale: Locale}
     * @throws InvalidArgumentException
     */
    public function getOrSet(Nutgram $bot): array
    {
        $userId = $bot->getUserData(self::KEY_USER_ID);
        $accountId = $bot->getUserData(self::KEY_ACCOUNT_ID);
        $locale = $bot->getUserData(self::KEY_LOCALE);

        if (
            !empty($userId)
            && !empty($accountId)
            && !empty($locale)
        ) {
            return [
                'userId' => $userId,
                'accountId' => $accountId,
                'locale' => $locale,
            ];
        }

        return $this->refresh($bot);
    }

    /**
     * @return array{userId: string, accountId: string, locale: Locale}
     * @throws InvalidArgumentException
     */
    public function refresh(Nutgram $bot): array
    {
        $telegramId = $bot->userId();
        if ($telegramId === null) {
            throw new UnexpectedValueException('Telegram user is missing from the update.');
        }

        $user = $this->users->getByTelegramId(new TelegramId($telegramId));
        $account = $user->getAccounts()[0] ?? throw new DomainException('User does not have any account.');

        $this->remember($bot, $user->id->value, $account->id->value, $user->locale, $telegramId);

        return [
            'userId' => $user->id->value,
            'accountId' => $account->id->value,
            'locale' => $user->locale,
        ];
    }
}
