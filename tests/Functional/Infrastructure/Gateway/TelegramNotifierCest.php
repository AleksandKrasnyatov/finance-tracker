<?php

declare(strict_types=1);

namespace Test\Functional\Infrastructure\Gateway;

use App\Application\Gateway\Notification;
use App\Application\Gateway\NotifierInterface;
use App\Application\Gateway\TranslatorInterface;
use App\Domain\Entity\User;
use App\Domain\ValueObject\Id;
use DomainException;
use SergiX44\Nutgram\Testing\FakeNutgram;
use Test\Support\Fixture\OnboardedTelegramUserFixture;
use Test\Support\FunctionalTester;
use Test\Support\TelegramBotTester;

final class TelegramNotifierCest
{
    private FakeNutgram $bot;

    public function _before(FunctionalTester $I): void
    {
        $I->loadFixtures(OnboardedTelegramUserFixture::class);
        $this->bot = TelegramBotTester::configure($I, OnboardedTelegramUserFixture::TELEGRAM_ID);
    }

    public function givenOnboardedUserWhenNotifiedThenTranslatedReminderIsSentToTelegramChat(
        FunctionalTester $I,
    ): void {
        $user = $I->grabEntityFromRepository(User::class, [
            'telegramId' => OnboardedTelegramUserFixture::TELEGRAM_ID,
        ]);

        /** @var NotifierInterface $notifier */
        $notifier = $I->grabService(NotifierInterface::class);
        /** @var TranslatorInterface $translator */
        $translator = $I->grabService(TranslatorInterface::class);

        $notifier->notify(
            $user->id,
            new Notification(Notification::REMINDER_NO_TRANSACTIONS_TODAY),
        );

        $this->bot->assertReplyMessage([
            'text' => $translator->trans(
                Notification::REMINDER_NO_TRANSACTIONS_TODAY,
                locale: $user->locale,
            ),
            'chat_id' => OnboardedTelegramUserFixture::TELEGRAM_ID,
        ]);
    }

    public function givenUnknownUserWhenNotifiedThenExceptionAndNoMessageToChat(
        FunctionalTester $I,
    ): void {
        /** @var NotifierInterface $notifier */
        $notifier = $I->grabService(NotifierInterface::class);

        $I->expectThrowable(DomainException::class, static function () use ($notifier): void {
            $notifier->notify(
                Id::generate(),
                new Notification(Notification::REMINDER_NO_TRANSACTIONS_TODAY),
            );
        });

        $this->bot->assertNoReply();
    }
}
