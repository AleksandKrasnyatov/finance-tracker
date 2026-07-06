<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Enum\AccountType;
use App\Domain\ValueObject\Id;
use App\Infrastructure\Doctrine\Type\IdType;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

#[ORM\Entity]
#[ORM\Table(name: 'accounts')]
final readonly class Account
{
    #[ORM\Column(type: IdType::NAME)]
    #[ORM\Id]
    public Id $id;
    #[ORM\Column(type: 'string', length: 100)]
    public string $name;
    #[ORM\Column(type:  Types::ENUM, enumType: AccountType::class)]
    public AccountType $type;
    #[ORM\Column(type: 'datetime_immutable')]
    public \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(targetEntity: UserAccount::class, mappedBy: 'account')]
    private Collection $members;

    public function __construct(
        Id $id,
        string $name,
        AccountType $type,
        DateTimeImmutable $createdAt,
    ) {
        Assert::notEmpty($name);

        $this->id = $id;
        $this->name = mb_strtolower($name);
        $this->type = $type;
        $this->createdAt = $createdAt;
    }
}
