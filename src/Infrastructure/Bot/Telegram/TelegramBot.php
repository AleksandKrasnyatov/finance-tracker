<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\RunningMode;
use Throwable;

final class TelegramBot
{
    private bool $configured = false;

    /**
     * @param array<string, class-string> $commands
     */
    public function __construct(
        private readonly Nutgram $bot,
        private readonly array $commands,
    ) {
    }

    public function configure(): void
    {
        if ($this->configured) {
            return;
        }

        foreach ($this->commands as $command => $handler) {
            $this->bot->onCommand($command, $handler);
        }

        $this->bot->onException(static function (Nutgram $bot, Throwable $exception): void {
            $bot->getContainer()
                ->get(LoggerInterface::class)
                ->error('Telegram update processing failed.', ['exception' => $exception]);

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
