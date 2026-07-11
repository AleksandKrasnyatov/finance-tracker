<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Enum\TransactionType;
use App\Domain\ValueObject\Id;
use App\Infrastructure\Doctrine\Type\IdType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

#[ORM\Entity]
#[ORM\Table(name: 'categories')]
#[ORM\UniqueConstraint(columns: ['account_id', 'name', 'type'])]
final class Category
{
    #[ORM\Column(type: IdType::NAME)]
    #[ORM\Id]
    private(set) Id $id;

    #[ORM\ManyToOne(inversedBy: 'categories')]
    #[ORM\JoinColumn(name: 'account_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private(set) Account $account;

    #[ORM\Column(type: Types::ENUM, enumType: TransactionType::class)]
    private(set) TransactionType $type;

    #[ORM\Column(type: Types::STRING, length: 30)]
    private(set) string $name;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: false)]
    private(set) User $creator;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'category', cascade: ['persist'])]
    private Collection $transactions;

    public function __construct(
        Id $id,
        Account $account,
        TransactionType $type,
        string $name,
        User $creator,
    ) {
        Assert::notEmpty($name);

        $this->id = $id;
        $this->account = $account;
        $this->type = $type;
        $this->name = mb_strtolower($name);
        $this->creator = $creator;
        $this->transactions = new ArrayCollection();
    }

    /**
     * @return list<array{TransactionType, non-empty-string}>
     */
    public static function defaults(): array
    {
        return [
            [TransactionType::Income, 'зарплата'],
            [TransactionType::Income, 'другое'],
            [TransactionType::Expense, 'продукты'],
            [TransactionType::Expense, 'кафе'],
            [TransactionType::Expense, 'транспорт'],
            [TransactionType::Expense, 'жильё'],
            [TransactionType::Expense, 'здоровье'],
            [TransactionType::Expense, 'развлечения'],
            [TransactionType::Expense, 'другое'],
        ];
    }

    public function rename(string $name): void
    {
        Assert::notEmpty($name);
        $this->name = mb_strtolower($name);
    }

    public function addTransaction(Transaction $transaction): void
    {
        $this->transactions->add($transaction);
    }

    public function removeTransaction(Transaction $transaction): void
    {
        $this->transactions->removeElement($transaction);
    }

    /**
     * @return Transaction[]
     */
    public function getTransactions(): array
    {
        return $this->transactions->toArray();
    }
}
