<?php

declare(strict_types=1);

namespace App\Infrastructure\Fetcher;

use App\Application\Fetcher\Account\AccountTransactionMonth;
use App\Application\Fetcher\Account\AccountTransactionMonthsFetcherInterface;
use App\Domain\ValueObject\Id;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

final readonly class AccountTransactionMonthsFetcher implements AccountTransactionMonthsFetcherInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return list<AccountTransactionMonth>
     * @throws Exception
     */
    public function fetch(Id $accountId, int $offset, int $limit): array
    {
        $limit = max(0, $limit);
        $offset = max(0, $offset);

        $rows = $this->entityManager->getConnection()->fetchAllAssociative(
            <<<SQL
                SELECT
                    CAST(EXTRACT(YEAR FROM t.date) AS INTEGER) AS year,
                    CAST(EXTRACT(MONTH FROM t.date) AS INTEGER) AS month
                FROM transactions t
                WHERE t.account_id = :accountId
                GROUP BY 1, 2
                ORDER BY 1 DESC, 2 DESC
                LIMIT {$limit} OFFSET {$offset}
                SQL,
            ['accountId' => $accountId->value],
        );

        $months = [];
        foreach ($rows as $row) {
            $year = $row['year'] ?? null;
            $month = $row['month'] ?? null;
            if (!is_numeric($year) || !is_numeric($month)) {
                continue;
            }

            $months[] = new AccountTransactionMonth((string)$year, (string)$month);
        }

        return $months;
    }
}
