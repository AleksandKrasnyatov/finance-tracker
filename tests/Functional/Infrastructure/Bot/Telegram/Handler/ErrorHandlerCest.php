<?php

declare(strict_types=1);

namespace Test\Functional\Infrastructure\Bot\Telegram\Handler;

use App\Application\Gateway\TranslatorInterface;
use App\Domain\Exception\AccountManageException;
use App\Domain\Exception\NoAccessException;
use Codeception\Attribute\Examples;
use Codeception\Example;
use Exception;
use SergiX44\Nutgram\Testing\FakeNutgram;
use Test\Support\Fixture\OnboardedTelegramUserFixture;
use Test\Support\FunctionalTester;
use Test\Support\TelegramBotTester;
use Throwable;

final class ErrorHandlerCest
{
    private FakeNutgram $bot;
    private TranslatorInterface $translator;

    public function _before(FunctionalTester $I): void
    {
        $this->bot = TelegramBotTester::configure($I, OnboardedTelegramUserFixture::TELEGRAM_ID);
        /** @var TranslatorInterface $translator */
        $translator = $I->grabService(TranslatorInterface::class);
        $this->translator = $translator;
    }

    /**
     * @param Example<array{exception: class-string<Throwable>, messageKey: string}> $example
     */
    #[Examples(exception: Exception::class, messageKey: 'bot.error.generic')]
    #[Examples(exception: NoAccessException::class, messageKey: 'bot.error.no_access')]
    #[Examples(exception: AccountManageException::class, messageKey: 'bot.error.account_manage')]
    public function givenHandlerThrowsExceptionWhenUpdateIsProcessedThenErrorMessageIsSent(Example $example): void
    {
        /** @var class-string<Throwable> $exceptionClass */
        $exceptionClass = $example['exception'];
        /** @var string $messageKey */
        $messageKey = $example['messageKey'];

        $this->bot->onUpdate(static fn() => throw new $exceptionClass());

        $this->bot
            ->hearText('/anything')
            ->reply()
            ->assertReplyText($this->translator->trans($messageKey));
    }
}
