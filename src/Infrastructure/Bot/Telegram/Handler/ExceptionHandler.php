<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler;

use App\Application\Gateway\TranslatorInterface;
use App\Domain\Enum\Locale;
use Psr\Log\LoggerInterface;
use SergiX44\Nutgram\Nutgram;
use Throwable;

final readonly class ExceptionHandler
{
    public function __construct(
        private LoggerInterface $logger,
        private TranslatorInterface $translator,
    ) {
    }

    public function __invoke(Nutgram $bot, Throwable $exception): void
    {
        $this->logger->error('Telegram update processing failed', [
            'message' => $exception->getMessage(),
            'exception' => $exception,
            'update_id' => $bot->update()?->update_id,
            'update_type' => $bot->update()?->getType()?->value,
            'user_id' => $bot->userId(),
            'chat_id' => $bot->chatId(),
            'text' => $bot->message()?->text,
            'callback_data' => $bot->callbackQuery()?->data,
            'callback_query_id' => $bot->callbackQuery()?->id,
        ]);

        $bot->sendMessage($this->translator->trans(
            'bot.error.generic',
            locale: Locale::fromLanguageCode($bot->user()?->language_code),
        ));
    }
}
