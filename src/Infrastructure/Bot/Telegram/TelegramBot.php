<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram;

use App\Infrastructure\Bot\Telegram\Conversation\AddCategoryConversation;
use App\Infrastructure\Bot\Telegram\Handler\StartHandler;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\RunningMode;
use Throwable;

final class TelegramBot
{
    private bool $configured = false;

    public function __construct(
        private readonly Nutgram $bot,
    ) {
    }

    public function configure(): void
    {
        if ($this->configured) {
            return;
        }

        $this->bot->onCommand('start', StartHandler::class);
        $this->bot->onCommand('category', AddCategoryConversation::class);
        $this->bot->onText('Добавить категорию', AddCategoryConversation::class);

        $this->bot->onException(static function (Nutgram $bot, Throwable $exception): void {
            $bot->getContainer()
                ->get(LoggerInterface::class)
                ->error('Telegram update processing failed: {message}', [
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

            $bot->sendMessage('Не удалось выполнить команду. Попробуйте ещё раз позднее.');
        });

        $this->configured = true;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function run(RunningMode $runningMode): void
    {
        $this->configure();
        $this->bot->setRunningMode($runningMode);
        $this->bot->run();
    }
}
