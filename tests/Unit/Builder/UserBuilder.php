<?php

declare(strict_types=1);

namespace Test\Unit\Builder;

use App\Domain\Entity\Reminder;
use App\Domain\Entity\User;
use App\Domain\Enum\Locale;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\TelegramId;
use DateTimeImmutable;

final class UserBuilder
{
    private Id $id;

    private DateTimeImmutable $createdAt;
    private ?TelegramId $telegramId = null;
    private Locale $locale = Locale::En;
    private ?Reminder $reminder = null;

    public function __construct()
    {
        $this->id = Id::generate();
        $this->createdAt = new DateTimeImmutable();
    }

    public function withTelegramId(TelegramId $telegramId): self
    {
        return clone($this, [
            "telegramId" => $telegramId
        ]);
    }

    public function withLocale(Locale $locale): self
    {
        return clone($this, [
            "locale" => $locale
        ]);
    }

    public function withReminder(Reminder $reminder): self
    {
        return clone($this, [
            "reminder" => $reminder
        ]);
    }

    public function build(): User
    {
        return new User(
            $this->id,
            $this->createdAt,
            $this->telegramId,
            $this->locale,
            $this->reminder,
        );
    }
}
