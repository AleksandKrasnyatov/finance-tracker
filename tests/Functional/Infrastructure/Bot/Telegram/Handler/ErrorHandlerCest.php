<?php

declare(strict_types=1);

namespace Test\Functional\Infrastructure\Bot\Telegram\Handler;

use App\Application\Gateway\TranslatorInterface;
use RuntimeException;
use SergiX44\Nutgram\Testing\FakeNutgram;
use Test\Support\Fixture\OnboardedTelegramUserFixture;
use Test\Support\FunctionalTester;
use Test\Support\TelegramBotTester;

final class ErrorHandlerCest
{
    private FakeNutgram $bot;
    private TranslatorInterface $translator;

    public function _before(FunctionalTester $I): void
    {
        $this->bot = TelegramBotTester::configure($I, OnboardedTelegramUserFixture::TELEGRAM_ID);
        $this->translator = $I->grabService(TranslatorInterface::class);
    }

    public function givenHandlerThrowsWhenUpdateIsProcessedThenGenericErrorIsSent(FunctionalTester $I): void
    {
        $this->bot->onUpdate(static fn () => throw new RuntimeException('test exception'));

        $this->bot
            ->hearText('/anything')
            ->reply()
            ->assertReplyText($this->translator->trans('bot.error.generic'));
    }
}
