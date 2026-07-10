<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'user_accounts')]
#[ORM\UniqueConstraint(columns: ['account_id', 'user_id'])]
final readonly class UserAccount
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    public string $id;
    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    public DateTimeImmutable $joinedAt;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'members')]
    #[ORM\JoinColumn(name: 'account_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    public Account $account;

    #[ORM\ManyToOne(inversedBy: 'accounts')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    public User $user;

    public function __construct(
        User $user,
        Account $account,
    ) {
        $this->id = Uuid::uuid4()->toString();
        $this->account = $account;
        $this->user = $user;
        $this->joinedAt = new DateTimeImmutable();
    }
}
