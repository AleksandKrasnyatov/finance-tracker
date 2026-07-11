<?php

declare(strict_types=1);

namespace Test\Unit\Application\UseCase\Auth\Command;

use App\Application\UseCase\Auth\Command\OnboardByTelegramCommand;
use App\Application\UseCase\Auth\Command\OnboardByTelegramHandler;
use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\TelegramId;
use App\Infrastructure\Repository\Flusher;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class OnboardByTelegramHandlerTest extends TestCase
{
    #[Test]
    public function givenNewTelegramUserWhenOnboardingThenCompleteInitialAccountIsSavedOnce(): void
    {
        $users = $this->createMock(UserRepositoryInterface::class);
        $users
            ->expects(self::once())
            ->method('hasByTelegramId')
            ->with(self::equalTo(new TelegramId(123456789)))
            ->willReturn(false);

        $createdUser = null;
        $users
            ->expects(self::once())
            ->method('add')
            ->willReturnCallback(static function (User $user) use (&$createdUser): void {
                $createdUser = $user;
            });

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $handler = new OnboardByTelegramHandler($users, new Flusher($entityManager));
        $handler->handle(new OnboardByTelegramCommand(123456789));

        self::assertInstanceOf(User::class, $createdUser);
        self::assertEquals(new TelegramId(123456789), $createdUser->telegramId);
        self::assertCount(1, $accounts = $createdUser->getAccounts());
        self::assertSame('основной', $accounts[0]->name);
        self::assertNotEmpty($accounts[0]->getCategories());
    }

    #[Test]
    public function givenExistingTelegramUserWhenOnboardingThenNothingIsPersisted(): void
    {
        $users = $this->createMock(UserRepositoryInterface::class);
        $users
            ->expects(self::once())
            ->method('hasByTelegramId')
            ->with(self::equalTo(new TelegramId(123456789)))
            ->willReturn(true);
        $users->expects(self::never())->method('add');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('flush');

        $handler = new OnboardByTelegramHandler($users, new Flusher($entityManager));
        $handler->handle(new OnboardByTelegramCommand(123456789));
    }
}
