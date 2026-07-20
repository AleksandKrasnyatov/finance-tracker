<?php

declare(strict_types=1);

namespace Test\Functional\Infrastructure\Bot\Telegram\Handler;

use App\Application\Gateway\TranslatorInterface;
use App\Domain\Entity\Category;
use App\Domain\Enum\TransactionType;
use App\Domain\Exception\AccountManageException;
use App\Domain\Exception\EntityNotFoundException;
use App\Domain\Exception\EnumInvalidValueException;
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
     * @param Example<array{exception: Throwable, messageKey: string}> $example
     */
    #[Examples(exception: new Exception(), messageKey: 'bot.error.generic')]
    #[Examples(exception: new NoAccessException(), messageKey: 'error.noAccess')]
    #[Examples(exception: new AccountManageException(), messageKey: 'error.accountManage')]
    #[Examples(exception: new EntityNotFoundException(Category::class), messageKey: 'error.notFound.Category')]
    #[Examples(exception: new EnumInvalidValueException(TransactionType::class), messageKey: 'error.invalidVal.TransactionType')]
    public function givenHandlerThrowsExceptionWhenUpdateIsProcessedThenErrorMessageIsSent(Example $example): void
    {
        /** @var Throwable $exception */
        $exception = $example['exception'];
        /** @var string $messageKey */
        $messageKey = $example['messageKey'];

        $this->bot->onUpdate(static fn() => throw $exception);

        $this->bot
            ->hearText('/anything')
            ->reply()
            ->assertReplyText($this->translator->trans($messageKey));
    }

    public function givenHandlerThrowsExceptionOnCallbackWhenUpdateIsProcessedThenAlertIsShown(): void
    {
        $this->bot->onUpdate(static fn() => throw new AccountManageException());

        $message = $this->translator->trans('error.accountManage');

        $this->bot
            ->hearCallbackQueryData('cat:anything')
            ->reply()
            ->assertReply('answerCallbackQuery', [
                'text' => $message,
                'show_alert' => true,
            ]);
    }
}
