<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler;

use App\Application\Gateway\TranslatorInterface;
use App\Application\UseCase\Auth\Command\ResetByTelegramCommand;
use App\Application\UseCase\Auth\Command\ResetByTelegramHandler;
use App\Domain\Enum\Locale;
use App\Infrastructure\Bot\Telegram\TelegramUserData;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Nutgram;
use UnexpectedValueException;

final readonly class ResetHandler
{
    public function __construct(
        private ResetByTelegramHandler $reset,
        private TelegramUserData $userData,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function __invoke(Nutgram $bot): void
    {
        $telegramId = $bot->userId();
        if ($telegramId === null) {
            throw new UnexpectedValueException('Telegram user is missing from the update.');
        }

        $locale = Locale::fromLanguageCode($bot->user()?->language_code);

        $this->reset->handle(new ResetByTelegramCommand($telegramId));
        $this->userData->clear($bot);

        $bot->sendMessage($this->translator->trans('bot.reset', locale: $locale));
    }
}
