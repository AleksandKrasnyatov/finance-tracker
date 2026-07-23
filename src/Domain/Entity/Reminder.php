<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Enum\Locale;
use App\Domain\ValueObject\ReminderTime;
use App\Domain\ValueObject\Timezone;
use App\Infrastructure\Doctrine\Type\ReminderTimeType;
use App\Infrastructure\Doctrine\Type\TimezoneType;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final class Reminder
{
    #[ORM\Column(type: Types::BOOLEAN)]
    private(set) bool $remindersEnabled;
    #[ORM\Column(type: ReminderTimeType::NAME, length: 5)]
    private(set) ReminderTime $reminderTime;
    #[ORM\Column(type: TimezoneType::NAME, length: 64)]
    private(set) Timezone $timezone;
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private(set) ?DateTimeImmutable $lastReminderSentAt;

    public function __construct(
        ReminderTime $reminderTime,
        Timezone $timezone,
        bool $remindersEnabled = true,
        ?DateTimeImmutable $lastReminderSentAt = null,
    ) {
        $this->remindersEnabled = $remindersEnabled;
        $this->reminderTime = $reminderTime;
        $this->timezone = $timezone;
        $this->lastReminderSentAt = $lastReminderSentAt?->setTimezone(new DateTimeZone('UTC'));
    }

    public static function create(?Locale $locale = null): self
    {
        return new self(
            ReminderTime::default(),
            Timezone::defaultForLocale($locale),
        );
    }

    public function markSent(DateTimeImmutable $sentAt): void
    {
        $this->lastReminderSentAt = $sentAt->setTimezone(new DateTimeZone('UTC'));
    }
}
