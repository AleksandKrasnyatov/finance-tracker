<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Enum\Locale;
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
#[ORM\UniqueConstraint(columns: ['telegram_id'])]
final class User
{
    #[ORM\Column(type: IdType::NAME)]
    #[ORM\Id]
    private(set) Id $id;
    #[ORM\Column(type: TelegramIdType::NAME, nullable: true)]
    private(set) ?TelegramId $telegramId;
    #[ORM\Column(type: Types::ENUM, enumType: Locale::class)]
    private(set) Locale $locale;
    #[ORM\Embedded]
    private(set) Reminder $reminder;
    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private(set) DateTimeImmutable $createdAt;
    /**
     * @var Collection<int, UserAccount>
     */
    #[ORM\OneToMany(targetEntity: UserAccount::class, mappedBy: 'user', cascade: ['all'], orphanRemoval: true)]
    private Collection $accounts;

    public function __construct(
        Id $id,
        DateTimeImmutable $createdAt,
        ?TelegramId $telegramId = null,
        Locale $locale = Locale::En,
        ?Reminder $reminder = null
    ) {
        $this->id = $id;
        $this->telegramId = $telegramId;
        $this->locale = $locale;
        $this->createdAt = $createdAt;
        $this->accounts = new ArrayCollection();
        $this->reminder = $reminder ?? Reminder::create($locale);
    }

    public static function joinByTelegram(
        TelegramId $telegramId,
        DateTimeImmutable $createdAt,
        Locale $locale = Locale::En,
    ): self {
        return new self(
            Id::generate(),
            $createdAt,
            $telegramId,
            $locale,
        );
    }

    public function changeLocale(Locale $locale): void
    {
        $this->locale = $locale;
    }

    public function markReminderSent(DateTimeImmutable $sentAt): void
    {
        $this->reminder->markSent($sentAt);
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
