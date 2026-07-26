<?php

declare(strict_types=1);

namespace Test\Functional\Infrastructure\Bot\Telegram\Handler;

use App\Application\Gateway\TranslatorInterface;
use App\Domain\Entity\User;
use App\Domain\ValueObject\Timezone;
use SergiX44\Nutgram\Testing\FakeNutgram;
use Test\Support\Fixture\OnboardedTelegramUserFixture;
use Test\Support\FunctionalTester;
use Test\Support\TelegramBotTester;

final class RemindersHandlerCest
{
    private FakeNutgram $bot;
    private TranslatorInterface $translator;

    public function _before(FunctionalTester $I): void
    {
        $I->loadFixtures(OnboardedTelegramUserFixture::class);
        $this->bot = TelegramBotTester::configure($I, OnboardedTelegramUserFixture::TELEGRAM_ID);
        /** @var TranslatorInterface $translator */
        $translator = $I->grabService(TranslatorInterface::class);
        $this->translator = $translator;
    }

    public function givenRemindersCommandWhenOpenedThenScreenIsShown(): void
    {
        $this->bot
            ->hearText('/reminders')
            ->reply()
            ->assertReplyText($this->translator->trans('bot.reminders.body'));
    }

    public function givenRemindersWhenDisabledThenRemindersAreTurnedOff(FunctionalTester $I): void
    {
        $this->bot
            ->hearText('/reminders')
            ->reply();

        $this->bot
            ->hearCallbackQueryData('rem:off')
            ->reply();

        $user = $I->grabEntityFromRepository(User::class, [
            'telegramId' => OnboardedTelegramUserFixture::TELEGRAM_ID,
        ]);
        $I->assertFalse($user->reminder->remindersEnabled);
    }

    public function givenRemindersWhenTimezoneSelectedThenTimezoneIsUpdated(FunctionalTester $I): void
    {
        $index = 4;
        $toSetTimeZone = Timezone::common()[$index];

        $this->bot
            ->hearText('/reminders')
            ->reply();

        $this->bot
            ->hearCallbackQueryData('rem:timezone')
            ->reply();

        $this->bot
            ->hearCallbackQueryData("rem:set_timezone:{$index}")
            ->reply();

        $user = $I->grabEntityFromRepository(User::class, [
            'telegramId' => OnboardedTelegramUserFixture::TELEGRAM_ID,
        ]);
        $I->assertSame($toSetTimeZone->value, $user->reminder->timezone->value);
    }
}
