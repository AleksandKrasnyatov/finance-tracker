<?php

declare(strict_types=1);

namespace Test\Unit\Domain\Entity;

use App\Domain\Entity\Reminder;
use App\Domain\ValueObject\ReminderTime;
use App\Domain\ValueObject\Timezone;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ReminderTest extends TestCase
{
    #[Test]
    public function givenNothingWhenDefaultIsCreatedThenEnabledAt2100UtcWithoutLastReminderSentAt(): void
    {
        $reminder = Reminder::create();

        self::assertTrue($reminder->remindersEnabled);
        self::assertSame(ReminderTime::default()->value, $reminder->reminderTime->value);
        self::assertSame(Timezone::defaultForLocale()->value, $reminder->timezone->value);
        self::assertNull($reminder->lastReminderSentAt);
    }

    #[Test]
    public function givenSentAtWhenMarkSentThenLastReminderSentAtIsStoredInUtc(): void
    {
        $reminder = Reminder::create();
        $sentAt = new DateTimeImmutable('2026-07-23 21:00:00', new DateTimeZone('Europe/Moscow'));

        $reminder->markSent($sentAt);

        self::assertEquals(
            new DateTimeImmutable('2026-07-23 18:00:00', new DateTimeZone('UTC')),
            $reminder->lastReminderSentAt,
        );
    }
}
