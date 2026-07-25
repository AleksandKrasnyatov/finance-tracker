<?php

declare(strict_types=1);

namespace Test\Functional\Infrastructure\Bot\Telegram\Handler;

use App\Application\Service\SeedCatalog;
use App\Application\UseCase\Account\Command\Category\ChangeCategoryNameCommand;
use App\Application\UseCase\Account\Command\Category\ChangeCategoryNameHandler;
use App\Domain\Entity\Account;
use App\Domain\Entity\Category;
use App\Domain\Entity\User;
use App\Domain\Enum\Locale;
use App\Infrastructure\Bot\Telegram\TelegramUserData;
use SergiX44\Nutgram\Testing\FakeNutgram;
use Test\Support\Fixture\OnboardedTelegramUserFixture;
use Test\Support\FunctionalTester;
use Test\Support\TelegramBotTester;

final class SettingsHandlerCest
{
    private FakeNutgram $bot;
    private SeedCatalog $seeds;

    public function _before(FunctionalTester $I): void
    {
        $I->loadFixtures(OnboardedTelegramUserFixture::class);
        $this->bot = TelegramBotTester::configure($I, OnboardedTelegramUserFixture::TELEGRAM_ID);
        /** @var SeedCatalog $seeds */
        $seeds = $I->grabService(SeedCatalog::class);
        $this->seeds = $seeds;
    }

    public function givenSettingsCommandWhenOpenedThenMenuIsShown(): void
    {
        $this->bot
            ->hearText('/settings')
            ->reply()
            ->assertReplyText('Settings');
    }

    public function givenLanguagePickerWhenRussianSelectedThenLocaleAndSeedNamesAreUpdated(
        FunctionalTester $I,
    ): void {
        $telegramId = OnboardedTelegramUserFixture::TELEGRAM_ID;

        $this->bot
            ->hearText('/settings')
            ->reply();

        $this->bot
            ->hearCallbackQueryData('settings:language')
            ->reply();

        $this->bot
            ->hearCallbackQueryData('settings:set_language:ru')
            ->reply();

        $I->seeInRepository(User::class, [
            'telegramId' => $telegramId,
            'locale' => Locale::Ru,
        ]);
        $I->seeInRepository(Account::class, [
            'name' => $this->seeds->accountName(Locale::Ru),
            'code' => SeedCatalog::ACCOUNT_CODE,
        ]);
        $I->seeInRepository(Category::class, [
            'name' => $this->seeds->localize('groceries', Locale::Ru),
            'code' => 'groceries',
        ]);
        $I->assertSame(
            Locale::Ru,
            $this->bot->getUserData(TelegramUserData::KEY_LOCALE, $telegramId),
        );
    }
}
