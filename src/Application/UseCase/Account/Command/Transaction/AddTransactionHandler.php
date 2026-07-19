<?php

declare(strict_types=1);

namespace App\Application\UseCase\Account\Command\Transaction;

use App\Domain\Enum\TransactionType;
use App\Domain\Repository\AccountRepositoryInterface;
use App\Domain\Repository\CategoryRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Money;
use App\Infrastructure\Repository\Flusher;
use DateMalformedStringException;
use DateTimeImmutable;

final readonly class AddTransactionHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private AccountRepositoryInterface $accounts,
        private CategoryRepositoryInterface $categories,
        private Flusher $flusher,
    ) {
    }

    /**
     * @throws DateMalformedStringException
     */
    public function handle(AddTransactionCommand $command): void
    {
        $user = $this->users->get(new Id($command->userId));
        $account = $this->accounts->get(new Id($command->accountId));
        $type = TransactionType::fromName($command->type);

        //todo можно упростить как будто, если передавать id категории
        $category = $this->categories->getByParams($account, $command->category, $type);
        $money = new Money($command->amount);
        $date = $command->date ? new DateTimeImmutable($command->date) : null;

        $account->addTransaction($user, $category, $money, $command->description, $date);

        $this->flusher->flush();
    }
}
