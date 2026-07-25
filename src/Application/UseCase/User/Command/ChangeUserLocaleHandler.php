<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\Command;

use App\Application\Service\SeedCatalog;
use App\Domain\Enum\Locale;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Id;
use App\Infrastructure\Repository\Flusher;

final readonly class ChangeUserLocaleHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private SeedCatalog $seeds,
        private Flusher $flusher,
    ) {
    }

    public function handle(ChangeUserLocaleCommand $command): void
    {
        $locale = Locale::fromLanguageCode($command->locale);
        $user = $this->users->get(new Id($command->userId));

        if ($user->locale === $locale) {
            return;
        }

        $user->changeLocale($locale);

        foreach ($user->getAccounts() as $account) {
            if ($account->code !== null) {
                $account->relocalizeName($user, $this->seeds->localize($account->code, $locale));
            }

            foreach ($account->getCategories() as $category) {
                if ($category->code === null) {
                    continue;
                }

                $account->relocalizeCategoryName(
                    $user,
                    $category->id,
                    $this->seeds->localize($category->code, $locale),
                );
            }
        }

        $this->flusher->flush();
    }
}
