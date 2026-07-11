<?php

declare(strict_types=1);

namespace Test\Unit\Infrastructure\Bot\Telegram\Handler;

use App\Domain\Repository\UserRepositoryInterface;
use App\Infrastructure\Bot\Telegram\TelegramBot;
use DI\Container;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SergiX44\Nutgram\Configuration;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\User\User as TelegramUser;

final class StartHandlerTest extends TestCase
{
    #[Test]
    public function givenStartCommandWhenHandledThenUserIsOnboardedAndWelcomed(): void
    {
        $users = $this->createStub(UserRepositoryInterface::class);
        $entityManager = $this->createStub(EntityManagerInterface::class);

        $container = new Container();
        $container->set(UserRepositoryInterface::class, $users);
        $container->set(EntityManagerInterface::class, $entityManager);

        $bot = Nutgram::fake(
            config: new Configuration(container: $container),
        );

        $telegramUser = new TelegramUser($bot);
        $telegramUser->id = 987654321;
        $telegramUser->is_bot = false;
        $telegramUser->first_name = 'Alex';
        $bot->setCommonUser($telegramUser);

        new TelegramBot($bot)->configure();

        $bot
            ->hearText('/start')
            ->reply()
            ->assertReplyText('Добро пожаловать! Основной счёт и категории готовы к работе.');
    }
}
