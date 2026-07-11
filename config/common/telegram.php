<?php

declare(strict_types=1);

use App\Infrastructure\Bot\Telegram\Http\TelegramWebhookAction;
use App\Infrastructure\Bot\Telegram\TelegramBotFactory;

use function DI\autowire;

$webhookSecret = getenv('TELEGRAM_WEBHOOK_SECRET') ?: '';
$botToken = getenv('TELEGRAM_BOT_TOKEN') ?: '';

return [
    TelegramBotFactory::class => autowire()->constructorParameter('token', $botToken),
    TelegramWebhookAction::class => autowire()->constructorParameter('secretToken', $webhookSecret),
];
