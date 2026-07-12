<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler;

use App\Application\UseCase\Auth\Command\OnboardByTelegramCommand;
use App\Application\UseCase\Auth\Command\OnboardByTelegramHandler;
use App\Infrastructure\Bot\Telegram\TelegramUserData;
use SergiX44\Nutgram\Nutgram;
use UnexpectedValueException;

final readonly class StartHandler
{
    private const string WELCOME_MESSAGE = 'Добро пожаловать! Основной счёт и категории готовы к работе.';

    public function __construct(
        private OnboardByTelegramHandler $onboard,
        private TelegramUserData $userData,
    ) {
    }

    public function __invoke(Nutgram $bot): void
    {
        $telegramId = $bot->userId();
        if ($telegramId === null) {
            throw new UnexpectedValueException('Telegram user is missing from the update.');
        }

        $this->onboard->handle(new OnboardByTelegramCommand($telegramId));
        $this->userData->refresh($bot);

        $bot->sendMessage(self::WELCOME_MESSAGE);
    }
}
