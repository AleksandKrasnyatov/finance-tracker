<?php

declare(strict_types=1);

namespace Test\Support;

use App\Domain\Enum\Locale;
use App\Infrastructure\Bot\Telegram\TelegramBot;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Chat\Chat;
use SergiX44\Nutgram\Telegram\Types\User\User as TelegramUser;
use SergiX44\Nutgram\Testing\FakeNutgram;

final class TelegramBotTester
{
    public static function configure(
        FunctionalTester $I,
        int $telegramId,
        ?Locale $locale = null,
    ): FakeNutgram {
        /** @var FakeNutgram $bot */
        $bot = $I->grabService(Nutgram::class);

        $telegramUser = new TelegramUser($bot);
        $telegramUser->id = $telegramId;
        $telegramUser->is_bot = false;
        $telegramUser->first_name = 'Alex';
        $telegramUser->language_code = $locale?->value;

        $chat = new Chat($bot);
        $chat->id = $telegramId;
        $chat->type = 'private';

        $bot->setCommonUser($telegramUser);
        $bot->setCommonChat($chat);

        /** @var TelegramBot $telegramBot */
        $telegramBot = $I->grabService(TelegramBot::class);
        $telegramBot->configure();

        return $bot;
    }
}
