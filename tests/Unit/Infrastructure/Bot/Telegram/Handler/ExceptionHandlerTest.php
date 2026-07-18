<?php

declare(strict_types=1);

namespace Test\Unit\Infrastructure\Bot\Telegram\Handler;

use App\Application\Gateway\TranslatorInterface;
use App\Infrastructure\Bot\Telegram\Handler\ExceptionHandler;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use SergiX44\Nutgram\Nutgram;

final class ExceptionHandlerTest extends TestCase
{
    #[Test]
    public function givenExceptionWhenHandledThenErrorIsLoggedAndGenericMessageIsSent(): void
    {
        $exception = new RuntimeException('boom');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error');

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->once())->method('trans')->willReturn($message = 'Sorry!');

        $bot = Nutgram::fake();

        new ExceptionHandler($logger, $translator)($bot, $exception);

        $bot->assertReplyText($message);
    }
}
