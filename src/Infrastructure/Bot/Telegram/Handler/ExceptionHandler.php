<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler;

use App\Application\Gateway\TranslatorInterface;
use App\Domain\Enum\Locale;
use App\Domain\Exception\AccountManageException;
use App\Domain\Exception\EntityNotFoundException;
use App\Domain\Exception\EnumInvalidValueException;
use App\Domain\Exception\NoAccessException;
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
        $update = $bot->update();
        $context = [
            'message' => $exception->getMessage(),
            'exception' => $exception,
            'update_id' => $update->update_id ?? null,
            'update_type' => $update?->getType()?->value,
            'user_id' => $bot->userId(),
            'chat_id' => $bot->chatId(),
            'text' => $bot->message()?->text,
            'callback_data' => $bot->callbackQuery()?->data,
            'callback_query_id' => $bot->callbackQuery()?->id,
        ];

        $this->logger->error('Telegram update processing failed', $context);
        $this->endConversation($bot);

        $errorMessage = $this->getErrorMessage(
            exception: $exception,
            locale: Locale::fromLanguageCode($bot->user()?->language_code),
        );

        if ($bot->isCallbackQuery()) {
            $bot->answerCallbackQuery(text: $errorMessage, show_alert: true);

            return;
        }

        $bot->sendMessage($errorMessage);
    }

    private function endConversation(Nutgram $bot): void
    {
        try {
            if ($bot->userId() !== null && $bot->chatId() !== null) {
                $bot->endConversation();
            }
        } catch (Throwable $exception) {
            $this->logger->error('Telegram conversation failed', [
                'message' => $exception->getMessage(),
                'exception' => $exception,
                'user_id' => $bot->userId(),
                'chat_id' => $bot->chatId(),
            ]);
        }
    }

    private function getErrorMessage(Throwable $exception, Locale $locale): string
    {
        $key = match (true) {
            $exception instanceof NoAccessException => 'error.noAccess',
            $exception instanceof AccountManageException => 'error.accountManage',
            $exception instanceof EntityNotFoundException => 'error.notFound.' . $exception->getEntityName(),
            $exception instanceof EnumInvalidValueException => 'error.invalidVal.' . $exception->getEntityName(),
            default => 'bot.error.generic',
        };

        return $this->translator->trans($key, locale: $locale);
    }
}
