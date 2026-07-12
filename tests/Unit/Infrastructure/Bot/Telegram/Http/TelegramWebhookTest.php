<?php

declare(strict_types=1);

namespace Test\Unit\Infrastructure\Bot\Telegram\Http;

use App\Infrastructure\Bot\Telegram\Http\TelegramWebhook;
use App\Infrastructure\Bot\Telegram\TelegramBot;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SergiX44\Nutgram\Nutgram;

final class TelegramWebhookTest extends TestCase
{
    #[Test]
    public function givenInvalidSecretWhenWebhookRunsThenBotDoesNotProcessUpdate(): void
    {
        $bot = Nutgram::fake();
        $bot->onUpdate(static function () use ($bot): void {
            $bot->sendMessage('test');
        });

        new TelegramBot($bot)->run(new TelegramWebhook(
            '{"update_id":1}',
            'invalid-secret',
            'expected-secret',
        ));

        $bot->assertNoReply();
    }

    #[Test]
    public function givenValidSecretWhenWebhookRunsThenBotProcessesUpdate(): void
    {
        $bot = Nutgram::fake();
        $bot->onUpdate(static function () use ($bot): void {
            $bot->sendMessage('test');
        });

        new TelegramBot($bot)->run(new TelegramWebhook(
            '{"update_id":1}',
            'expected-secret',
            'expected-secret',
        ));

        $bot->assertReplyText('test');
    }
}
