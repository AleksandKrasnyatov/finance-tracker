<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Enum\Locale;
use App\Domain\ValueObject\ReminderTime;
use App\Domain\ValueObject\Timezone;
use App\Infrastructure\Doctrine\Type\ReminderTimeType;
use App\Infrastructure\Doctrine\Type\TimezoneType;
use DateTimeImmutable;
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
    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private(set) ?DateTimeImmutable $lastReminderOn;

    public function __construct(
        ReminderTime $reminderTime,
        Timezone $timezone,
        bool $remindersEnabled = true,
        ?DateTimeImmutable $lastReminderOn = null,
    ) {
        $this->remindersEnabled = $remindersEnabled;
        $this->lastReminderOn = $lastReminderOn;
        $this->reminderTime = $reminderTime;
        $this->timezone = $timezone;
    }

    public static function create(?Locale $locale = null): self
    {
        return new self(
            ReminderTime::default(),
            Timezone::defaultForLocale($locale),
        );
    }
}
