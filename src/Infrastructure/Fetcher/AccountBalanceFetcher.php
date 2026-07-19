<?php

declare(strict_types=1);

namespace App\Infrastructure\Fetcher;

use App\Application\Fetcher\AccountBalance;
use App\Application\Fetcher\AccountBalanceFetcherInterface;
use App\Domain\Enum\Currency;
use App\Domain\Enum\TransactionType;
use App\Domain\ValueObject\Id;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

final readonly class AccountBalanceFetcher implements AccountBalanceFetcherInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws Exception
     */
    public function fetchCurrentMonth(Id $accountId): AccountBalance
    {
        $from = new DateTimeImmutable('first day of this month midnight');
        $to = new DateTimeImmutable('first day of next month midnight');

        $row = $this->entityManager->getConnection()->fetchAssociative(
            <<<'SQL'
                SELECT
                    COALESCE(SUM(CASE WHEN c.type = :income THEN t.money_amount ELSE 0 END), 0) AS incomes,
                    COALESCE(SUM(CASE WHEN c.type = :expense THEN t.money_amount ELSE 0 END), 0) AS expenses
                FROM transactions t
                INNER JOIN categories c ON c.id = t.category_id
                WHERE t.account_id = :accountId
                  AND t.date >= :from
                  AND t.date < :to
                SQL,
            [
                'accountId' => $accountId->value,
                'income' => TransactionType::Income->value,
                'expense' => TransactionType::Expense->value,
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ],
        );

        $incomes = $this->toInt($row['incomes'] ?? 0);
        $expenses = $this->toInt($row['expenses'] ?? 0);

        return new AccountBalance(
            $accountId->value,
            (int)$from->format('Y'),
            (int)$from->format('n'),
            $incomes - $expenses,
            $incomes,
            $expenses,
            Currency::RUB->value,
        );
    }

    private function toInt(mixed $amount): int
    {
        return is_numeric($amount) ? (int)round((float) $amount) : 0;
    }
}
