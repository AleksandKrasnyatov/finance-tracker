<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Money;
use App\Infrastructure\Doctrine\Type\IdType;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'transactions')]
final class Transaction
{
    #[ORM\Column(type: IdType::NAME)]
    #[ORM\Id]
    private(set) Id $id;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(name: 'account_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private(set) Account $account;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: false)]
    private(set) Category $category;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private(set) string $description;

    #[ORM\Embedded]
    private(set) Money $money;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: false)]
    private(set) User $creator;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private(set) DateTimeImmutable $createdAt;

    public function __construct(
        Account $account,
        User $user,
        Category $category,
        Money $money,
        string $description = '',
    ) {
        $this->id = Id::generate();
        $this->account = $account;
        $this->category = $category;
        $this->money = $money;
        $this->creator = $user;
        $this->description = $description;
        $this->createdAt = new DateTimeImmutable();
    }
}
