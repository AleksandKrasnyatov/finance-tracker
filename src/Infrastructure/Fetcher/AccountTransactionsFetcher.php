<?php

declare(strict_types=1);

namespace App\Infrastructure\Fetcher;

use App\Application\Fetcher\Account\AccountTransaction;
use App\Application\Fetcher\Account\AccountTransactionsFetcherInterface;
use App\Domain\Enum\Currency;
use App\Domain\Enum\TransactionType;
use App\Domain\ValueObject\Id;
use DateMalformedStringException;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

final readonly class AccountTransactionsFetcher implements AccountTransactionsFetcherInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return list<AccountTransaction>
     * @throws Exception|DateMalformedStringException
     */
    public function fetch(
        Id $accountId,
        string $year,
        string $month,
        ?TransactionType $type = null,
    ): array {
        $from = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
        $to = $from->modify('first day of next month');

        $sql = <<<'SQL'
            SELECT
                t.id,
                c.type,
                t.money_amount AS amount,
                t.money_currency AS currency,
                c.id AS category_id,
                c.name AS category_name,
                t.date,
                t.description
            FROM transactions t
            INNER JOIN categories c ON c.id = t.category_id
            WHERE t.account_id = :accountId
              AND t.date >= :from
              AND t.date < :to
            SQL;
        $params = [
            'accountId' => $accountId->value,
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
        ];

        if ($type !== null) {
            $sql .= ' AND c.type = :type';
            $params['type'] = $type->value;
        }

        $sql .= ' ORDER BY t.date DESC, t.created_at DESC';

        return $this->mapRows(
            $this->entityManager->getConnection()->fetchAllAssociative($sql, $params),
        );
    }

    /**
     * @throws Exception|DateMalformedStringException
     */
    public function fetchOne(Id $accountId, Id $transactionId): ?AccountTransaction
    {
        $row = $this->entityManager->getConnection()->fetchAssociative(
            <<<'SQL'
                SELECT
                    t.id,
                    c.type,
                    t.money_amount AS amount,
                    t.money_currency AS currency,
                    c.id AS category_id,
                    c.name AS category_name,
                    t.date,
                    t.description
                FROM transactions t
                INNER JOIN categories c ON c.id = t.category_id
                WHERE t.account_id = :accountId
                  AND t.id = :transactionId
                SQL,
            [
                'accountId' => $accountId->value,
                'transactionId' => $transactionId->value,
            ],
        );

        if ($row === false) {
            return null;
        }

        $mapped = $this->mapRows([$row]);

        return $mapped[0] ?? null;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<AccountTransaction>
     * @throws DateMalformedStringException
     */
    private function mapRows(array $rows): array
    {
        $transactions = [];
        foreach ($rows as $row) {
            $id = $row['id'] ?? null;
            $typeValue = $row['type'] ?? null;
            $amount = $row['amount'] ?? null;
            $currency = $row['currency'] ?? null;
            $categoryId = $row['category_id'] ?? null;
            $categoryName = $row['category_name'] ?? null;
            $date = $row['date'] ?? null;
            $description = $row['description'] ?? '';

            if (
                !is_string($id)
                || !is_string($typeValue)
                || !is_numeric($amount)
                || !is_string($currency)
                || !is_string($categoryId)
                || !is_string($categoryName)
                || !is_string($date)
                || !is_string($description)
            ) {
                continue;
            }

            $transactions[] = new AccountTransaction(
                $id,
                TransactionType::fromName($typeValue),
                (string)$amount,
                Currency::from($currency),
                $categoryId,
                $categoryName,
                new DateTimeImmutable($date),
                $description
            );
        }

        return $transactions;
    }
}
