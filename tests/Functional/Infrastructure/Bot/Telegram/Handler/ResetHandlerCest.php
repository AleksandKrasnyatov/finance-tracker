<?php

declare(strict_types=1);

namespace Test\Functional\Infrastructure\Bot\Telegram\Handler;

use App\Application\Gateway\TranslatorInterface;
use App\Domain\Entity\Account;
use App\Domain\Entity\Category;
use App\Domain\Entity\Transaction;
use App\Domain\Entity\User;
use App\Domain\Enum\Locale;
use App\Infrastructure\Bot\Telegram\TelegramUserData;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Testing\FakeNutgram;
use Test\Support\Fixture\OnboardedTelegramUserFixture;
use Test\Support\FunctionalTester;
use Test\Support\TelegramBotTester;

final class ResetHandlerCest
{
    private FakeNutgram $bot;
    private TranslatorInterface $translator;

    public function _before(FunctionalTester $I): void
    {
        $I->loadFixtures(OnboardedTelegramUserFixture::class);
        $this->bot = TelegramBotTester::configure($I, OnboardedTelegramUserFixture::TELEGRAM_ID);
        $this->translator = $I->grabService(TranslatorInterface::class);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function givenOnboardedUserWhenResetCommandThenUserAccountAndRelatedDataAreDeleted(
        FunctionalTester $I,
    ): void {
        $telegramId = OnboardedTelegramUserFixture::TELEGRAM_ID;

        $user = $I->grabEntityFromRepository(User::class, ['telegramId' => $telegramId]);
        $account = $user->getAccounts()[0];

        $I->seeInRepository(Category::class, ['account' => $account]);

        $this->bot
            ->hearText('/reset')
            ->reply()
            ->assertReplyText($this->translator->trans('bot.reset'));

        $I->clearEntityManager();

        $I->dontSeeInRepository(User::class, ['telegramId' => $telegramId]);
        $I->dontSeeInRepository(Account::class, ['id' => $account->id->value]);
        $I->dontSeeInRepository(Category::class, ['account' => $account]);
        $I->dontSeeInRepository(Transaction::class, ['account' => $account]);

        $I->assertNull($this->bot->getUserData(TelegramUserData::KEY_USER_ID, $telegramId));
        $I->assertNull($this->bot->getUserData(TelegramUserData::KEY_ACCOUNT_ID, $telegramId));
        $I->assertNull($this->bot->getUserData(TelegramUserData::KEY_LOCALE, $telegramId));
    }
}
