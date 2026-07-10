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
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'users')]
final class User
{
    #[ORM\Column(type: IdType::NAME)]
    #[ORM\Id]
    public readonly Id $id;
    #[ORM\Column(type: TelegramIdType::NAME, nullable: true)]
    public readonly ?TelegramId $telegramId;
    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    public readonly DateTimeImmutable $createdAt;
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

    public function equals(User $user): bool
    {
        return $this->id->equals($user->id);
    }

    public function addAccount(Account $account): void
    {
        $userAccount = new UserAccount($this, $account);
        $this->accounts->add($userAccount);
        $account->addMember($userAccount);
    }

    /**
     * @return Account[]
     */
    public function getAccounts(): array
    {
        /** @var Account[] */
        return $this->accounts->map(static fn (UserAccount $network) => $network->account)->toArray();
    }
}
