<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\TelegramId;
use App\Infrastructure\Doctrine\Type\IdType;
use App\Infrastructure\Doctrine\Type\TelegramIdType;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'users')]
final readonly class User
{
    #[ORM\Column(type: IdType::NAME)]
    #[ORM\Id]
    public Id $id;
    #[ORM\Column(type: TelegramIdType::NAME, nullable: true)]
    public ?TelegramId $telegramId;
    #[ORM\Column(type: 'datetime_immutable')]
    public DateTimeImmutable $createdAt;
    /**
     * @var Collection<int, UserAccount>
     */
    #[ORM\OneToMany(targetEntity: UserAccount::class, mappedBy: 'user', cascade: ['all'], orphanRemoval: true)]
    private Collection $accounts;

    public function __construct(
        Id $id,
        DateTimeImmutable $createdAt,
        ?TelegramId $telegramId = null,
    ) {
        $this->id = $id;
        $this->telegramId = $telegramId;
        $this->createdAt = $createdAt;
        $this->accounts = new ArrayCollection();
    }

    public static function joinByTelegram(TelegramId $telegramId, DateTimeImmutable $createdAt): self
    {
        return new self(
            Id::generate(),
            $createdAt,
            $telegramId,
        );
    }

    public function addAccount(Account $account): void
    {
        $this->accounts->add($account);
    }
}
