<?php

declare(strict_types=1);

namespace Test\Functional\Infrastructure\Bot\Telegram\Handler;

use App\Domain\Entity\Account;
use App\Domain\Entity\Category;
use App\Domain\Entity\User;
use App\Domain\Enum\AccountType;
use App\Infrastructure\Bot\Telegram\TelegramUserData;
use Psr\SimpleCache\InvalidArgumentException;
use Test\Support\Fixture\OnboardedTelegramUserFixture;
use Test\Support\FunctionalTester;
use Test\Support\TelegramBotTester;

final class StartHandlerCest
{
    /**
     * @throws InvalidArgumentException
     */
    public function givenStartCommandWhenHandledThenUserIsOnboardedWelcomedAndCached(FunctionalTester $I): void
    {
        $telegramId = OnboardedTelegramUserFixture::TELEGRAM_ID;
        $bot = TelegramBotTester::configure($I, $telegramId);

        $bot
            ->hearText('/start')
            ->reply()
            ->assertReplyText('Добро пожаловать! Основной счёт и категории готовы к работе.');

        $I->seeInRepository(User::class, [
            'telegramId' => $telegramId,
        ]);
        $I->seeInRepository(Account::class, [
            'name' => 'основной',
            'type' => AccountType::Personal,
        ]);

        foreach (Category::defaults() as $category) {
            $I->seeInRepository(Category::class, ['type' => $category[0]->value, 'name' => $category[1]]);
        }

        $user = $I->grabEntityFromRepository(User::class, ['telegramId' => $telegramId]);
        $account = $user->getAccounts()[0];

        $I->assertSame($user->id->value, $bot->getUserData(TelegramUserData::KEY_USER_ID, $telegramId));
        $I->assertSame($account->id->value, $bot->getUserData(TelegramUserData::KEY_ACCOUNT_ID, $telegramId));
    }
}
