<?php

declare(strict_types=1);

namespace App\Infrastructure\Fetcher;

use App\Application\Fetcher\AccountCategories;
use App\Application\Fetcher\AccountCategoriesFetcherInterface;
use App\Application\Fetcher\AccountCategory;
use App\Domain\Enum\TransactionType;
use App\Domain\ValueObject\Id;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

final readonly class AccountCategoriesFetcher implements AccountCategoriesFetcherInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws Exception
     */
    public function fetch(Id $accountId): AccountCategories
    {
        $rows = $this->entityManager->getConnection()->fetchAllAssociative(
            <<<'SQL'
                SELECT id, name, type
                FROM categories
                WHERE account_id = :accountId
                ORDER BY name
                SQL,
            ['accountId' => $accountId->value],
        );

        $incomes = [];
        $expenses = [];

        foreach ($rows as $row) {
            $id = $row['id'] ?? null;
            $name = $row['name'] ?? null;
            $type = $row['type'] ?? null;

            if (!is_string($id) || !is_string($name) || !is_string($type)) {
                continue;
            }

            $type = TransactionType::fromName($type);
            $category = new AccountCategory(new Id($id), $name, $type);

            if ($type === TransactionType::Income) {
                $incomes[] = $category;
            } else {
                $expenses[] = $category;
            }
        }

        return new AccountCategories($incomes, $expenses);
    }
}
