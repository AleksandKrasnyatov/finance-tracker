<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Enum\TransactionType;
use App\Domain\ValueObject\Id;
use App\Infrastructure\Doctrine\Type\IdType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

#[ORM\Entity]
#[ORM\Table(name: 'categories')]
#[ORM\UniqueConstraint(columns: ['account_id', 'name', 'type'])]
final readonly class Category
{
    #[ORM\Column(type: IdType::NAME)]
    #[ORM\Id]
    public Id $id;

    #[ORM\ManyToOne(inversedBy: 'categories')]
    #[ORM\JoinColumn(name: 'account_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    public Account $account;

    #[ORM\Column(type: Types::ENUM, enumType: TransactionType::class)]
    public TransactionType $type;

    #[ORM\Column(type: Types::STRING, length: 30)]
    public string $name;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $creator;

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
    }

    public function getCreator(): User
    {
        return $this->creator;
    }
}
