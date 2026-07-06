<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'user_accounts')]
#[ORM\UniqueConstraint(columns: ['account_id', 'user_id'])]
final readonly class UserAccount
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: Account::class, inversedBy: 'members')]
    #[ORM\JoinColumn(name: 'account_id', nullable: false)]
    private Account $account;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'accounts')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $joinedAt;

    public function __construct(
        Account $account,
        User $user,
    ) {
        $this->id = Uuid::uuid4();
        $this->account = $account;
        $this->user = $user;
        $this->joinedAt = new DateTimeImmutable();
    }
}
