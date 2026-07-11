<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Http;

use App\Infrastructure\Bot\Telegram\TelegramBotFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

final readonly class TelegramWebhookAction
{
    public function __construct(
        private TelegramBotFactory $telegramBotFactory,
        private string $secretToken,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if ($this->secretToken === '') {
            throw new RuntimeException('TELEGRAM_WEBHOOK_SECRET is not configured.');
        }

        $telegramWebhook = new TelegramWebhook(
            (string) $request->getBody(),
            $request->getHeaderLine('X-Telegram-Bot-Api-Secret-Token'),
            $this->secretToken,
        );

        $this->telegramBotFactory->create()->run($telegramWebhook);

        return $response;
    }
}
