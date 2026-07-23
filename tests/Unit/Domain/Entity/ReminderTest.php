<?php

declare(strict_types=1);

namespace Test\Unit\Domain\Entity;

use App\Domain\Entity\Reminder;
use App\Domain\ValueObject\ReminderTime;
use App\Domain\ValueObject\Timezone;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ReminderTest extends TestCase
{
    #[Test]
    public function givenNothingWhenDefaultIsCreatedThenEnabledAt2100UtcWithoutLastReminder(): void
    {
        $reminder = Reminder::create();

        self::assertTrue($reminder->remindersEnabled);
        self::assertSame(ReminderTime::default()->value, $reminder->reminderTime->value);
        self::assertSame(Timezone::defaultForLocale()->value, $reminder->timezone->value);
        self::assertNull($reminder->lastReminderOn);
    }
}
