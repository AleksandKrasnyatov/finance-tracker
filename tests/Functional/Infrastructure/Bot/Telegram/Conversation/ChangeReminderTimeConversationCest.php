<?php

declare(strict_types=1);

namespace Test\Functional\Infrastructure\Bot\Telegram\Conversation;

use App\Domain\Entity\User;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Testing\FakeNutgram;
use Test\Support\Fixture\OnboardedTelegramUserFixture;
use Test\Support\FunctionalTester;
use Test\Support\TelegramBotTester;

final class ChangeReminderTimeConversationCest
{
    private FakeNutgram $bot;

    public function _before(FunctionalTester $I): void
    {
        $I->loadFixtures(OnboardedTelegramUserFixture::class);
        $this->bot = TelegramBotTester::configure($I, OnboardedTelegramUserFixture::TELEGRAM_ID);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function givenRemindersWhenTimeEnteredThenReminderTimeIsUpdated(FunctionalTester $I): void
    {
        $telegramId = OnboardedTelegramUserFixture::TELEGRAM_ID;

        $this->bot->willStartConversation()
            ->hearText('/reminders')
            ->reply();

        $this->bot
            ->hearCallbackQueryData('rem:time')
            ->reply()
            ->assertReply('sendMessage', [
                'text' => 'Enter the reminder time as HH:MM, for example 12:23:',
            ], 1)
            ->assertActiveConversation($telegramId, $telegramId);

        $this->bot
            ->hearText('19:17')
            ->reply()
            ->assertReplyText('Reminder time updated: 19:17.')
            ->assertNoConversation($telegramId, $telegramId);

        $user = $I->grabEntityFromRepository(User::class, ['telegramId' => $telegramId]);
        $I->assertSame('19:17', $user->reminder->reminderTime->value);
    }
}
