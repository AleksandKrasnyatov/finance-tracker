<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler;

use App\Application\Gateway\TranslatorInterface;
use App\Application\UseCase\Auth\Command\OnboardByTelegramCommand;
use App\Application\UseCase\Auth\Command\OnboardByTelegramHandler;
use App\Infrastructure\Bot\Telegram\TelegramUserData;
use App\Infrastructure\Translation\LocaleMapper;
use SergiX44\Nutgram\Nutgram;
use UnexpectedValueException;

final readonly class StartHandler
{
    public function __construct(
        private OnboardByTelegramHandler $onboard,
        private TelegramUserData $userData,
        private LocaleMapper $localeMapper,
        private TranslatorInterface $translator,
    ) {
    }

    public function __invoke(Nutgram $bot): void
    {
        $telegramId = $bot->userId();
        if ($telegramId === null) {
            throw new UnexpectedValueException('Telegram user is missing from the update.');
        }

        $locale = $this->localeMapper->fromTelegramLanguageCode($bot->user()?->language_code);

        $this->onboard->handle(new OnboardByTelegramCommand($telegramId, $locale->value));
        $this->userData->refresh($bot);

        $bot->sendMessage($this->translator->trans('bot.welcome', locale: $locale));
    }
}
