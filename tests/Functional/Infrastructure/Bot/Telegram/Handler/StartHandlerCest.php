<?php

declare(strict_types=1);

namespace Test\Functional\Infrastructure\Bot\Telegram\Handler;

use App\Application\Gateway\TranslatorInterface;
use App\Application\Service\SeedCatalog;
use App\Domain\Entity\Account;
use App\Domain\Entity\Category;
use App\Domain\Entity\User;
use App\Domain\Enum\AccountType;
use App\Domain\Enum\Locale;
use App\Infrastructure\Bot\Telegram\TelegramUserData;
use App\Infrastructure\Translation\SymfonyTranslator;
use Codeception\Attribute\Examples;
use Codeception\Example;
use Psr\SimpleCache\InvalidArgumentException;
use Test\Support\Fixture\OnboardedTelegramUserFixture;
use Test\Support\FunctionalTester;
use Test\Support\TelegramBotTester;

use function DI\value;

final class StartHandlerCest
{
    private TranslatorInterface $translator;
    private SeedCatalog $seeds;

    public function _before(FunctionalTester $I): void
    {
        /** @var SymfonyTranslator $translator */
        $translator = $I->grabService(TranslatorInterface::class);
        /** @var SeedCatalog $seeds */
        $seeds = $I->grabService(SeedCatalog::class);

        $this->translator = $translator;
        $this->seeds = $seeds;
    }

    /**
     * @param Example<array{locale: Locale|null}> $example
     * @throws InvalidArgumentException
     */
    #[Examples(locale: null)]
    #[Examples(locale: Locale::En)]
    #[Examples(locale: Locale::Ru)]
    public function givenStartCommandWhenHandledThenUserIsOnboardedWelcomedAndCached(
        FunctionalTester $I,
        Example $example,
    ): void {
        $telegramId = OnboardedTelegramUserFixture::TELEGRAM_ID;
        /** @var Locale|null $locale */
        $locale = $example['locale'];

        $bot = TelegramBotTester::configure($I, $telegramId, $locale);

        $bot
            ->hearText('/start')
            ->reply()
            ->assertReplyText($this->translator->trans('bot.welcome', locale: $locale));

        $I->seeInRepository(User::class, [
            'telegramId' => $telegramId,
            'locale' => $locale ?? Locale::default(),
        ]);
        $I->seeInRepository(Account::class, [
            'name' => $this->seeds->accountName($locale ?? Locale::default()),
            'type' => AccountType::Personal,
        ]);

        foreach (Category::defaults() as $category) {
            $I->seeInRepository(Category::class, [
                'type' => $category->type->value,
                'name' => $this->translator->trans($category->name, locale: $locale),
            ]);
        }

        $user = $I->grabEntityFromRepository(User::class, ['telegramId' => $telegramId]);
        $account = $user->getAccounts()[0];

        $I->assertSame($user->id->value, $bot->getUserData(TelegramUserData::KEY_USER_ID, $telegramId));
        $I->assertSame($account->id->value, $bot->getUserData(TelegramUserData::KEY_ACCOUNT_ID, $telegramId));
        $I->assertSame(
            $locale ?? Locale::default(),
            $bot->getUserData(TelegramUserData::KEY_LOCALE, $telegramId),
        );
    }
}
