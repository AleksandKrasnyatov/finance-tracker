<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Enum\TransactionType;
use App\Domain\ValueObject\Id;
use App\Infrastructure\Doctrine\Type\IdType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

final readonly class Category
{
    #[ORM\Column(type: IdType::NAME)]
    #[ORM\Id]
    public Id $id;
    #[ORM\Column(type: Types::ENUM, enumType: TransactionType::class)]
    public TransactionType $type;
    #[ORM\Column(type: Types::STRING, length: 30)]
    public string $name;
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $creator;

    public function __construct(
        Id $id,
        TransactionType $type,
        string $name,
        User $creator
    ) {
        Assert::notEmpty($name);

        $this->id = $id;
        $this->type = $type;
        $this->name = $name;
        $this->creator = $creator;
    }

    public function getCreator(): User
    {
        return $this->creator;
    }
}
