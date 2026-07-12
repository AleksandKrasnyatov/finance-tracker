<?php

declare(strict_types=1);

namespace Test\Unit\Infrastructure\Bot\Telegram\Handler;

use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\TelegramId;
use App\Infrastructure\Bot\Telegram\TelegramBot;
use App\Infrastructure\Bot\Telegram\TelegramUserData;
use DI\Container;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SergiX44\Nutgram\Configuration;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\User\User as TelegramUser;
use Test\Unit\Builder\AccountBuilder;
use Test\Unit\Builder\UserBuilder;

final class StartHandlerTest extends TestCase
{
    #[Test]
    public function givenStartCommandWhenHandledThenUserIsOnboardedWelcomedAndCached(): void
    {
        $telegramId = 123456789;
        $user = new UserBuilder()->withTelegramId(new TelegramId($telegramId))->build();
        $account = new AccountBuilder()->withUser($user)->build();

        $users = $this->createStub(UserRepositoryInterface::class);
        $users->method('hasByTelegramId')->willReturn(true);
        $users->method('getByTelegramId')->willReturn($user);

        $entityManager = $this->createStub(EntityManagerInterface::class);

        $container = new Container();
        $container->set(UserRepositoryInterface::class, $users);
        $container->set(EntityManagerInterface::class, $entityManager);

        $bot = Nutgram::fake(
            config: new Configuration(container: $container),
        );

        $telegramUser = new TelegramUser($bot);
        $telegramUser->id = $telegramId;
        $telegramUser->is_bot = false;
        $telegramUser->first_name = 'Alex';
        $bot->setCommonUser($telegramUser);

        new TelegramBot($bot)->configure();

        $bot
            ->hearText('/start')
            ->reply()
            ->assertReplyText('Добро пожаловать! Основной счёт и категории готовы к работе.');

        self::assertSame($user->id->value, $bot->getUserData(TelegramUserData::KEY_USER_ID, $telegramId));
        self::assertSame($account->id->value, $bot->getUserData(TelegramUserData::KEY_ACCOUNT_ID, $telegramId));
    }
}
