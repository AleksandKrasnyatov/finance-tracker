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
final readonly class Transaction
{
    #[ORM\Column(type: IdType::NAME)]
    #[ORM\Id]
    public Id $id;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(name: 'account_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    public Account $account;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: false)]
    public Category $category;

    #[ORM\Column(type: Types::STRING, length: 255)]
    public string $description;

    #[ORM\Embedded]
    public Money $money;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: false)]
    public User $creator;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    public DateTimeImmutable $createdAt;

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
