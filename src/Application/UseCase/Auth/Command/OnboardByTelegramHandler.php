<?php

declare(strict_types=1);

namespace App\Application\UseCase\Auth\Command;

use App\Application\Service\SeedCatalog;
use App\Domain\Entity\Account;
use App\Domain\Entity\User;
use App\Domain\Enum\AccountType;
use App\Domain\Enum\Locale;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\TelegramId;
use App\Infrastructure\Repository\Flusher;
use DateTimeImmutable;

final readonly class OnboardByTelegramHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private SeedCatalog $seeds,
        private Flusher $flusher,
    ) {
    }

    public function handle(OnboardByTelegramCommand $command): void
    {
        $telegramId = new TelegramId($command->telegramId);
        if ($this->users->hasByTelegramId($telegramId)) {
            return;
        }

        $locale = Locale::tryFrom($command->locale) ?? Locale::default();

        $user = User::joinByTelegram($telegramId, new DateTimeImmutable(), $locale);
        $account = Account::create(
            $user,
            $this->seeds->accountName($locale),
            AccountType::Personal,
        );
        $account->addDefaultCategories($user, $this->seeds->categories($locale));

        $this->users->add($user);
        $this->flusher->flush();
    }
}
