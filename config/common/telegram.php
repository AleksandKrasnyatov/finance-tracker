<?php

declare(strict_types=1);

use App\Infrastructure\Bot\Telegram\Console\TelegramWebhookCommand;
use App\Infrastructure\Bot\Telegram\Http\TelegramWebhookAction;
use App\Infrastructure\Bot\Telegram\NutgramFactory;
use SergiX44\Nutgram\Nutgram;

use function DI\autowire;
use function DI\factory;

$webhookSecret = getenv('TELEGRAM_WEBHOOK_SECRET') ?: '';
$botToken = getenv('TELEGRAM_BOT_TOKEN') ?: '';
$webhookUrl = getenv('TELEGRAM_WEBHOOK_URL') ?: '';

$allowedUpdates = ['message', 'callback_query'];

return [
    NutgramFactory::class => autowire()
        ->constructorParameter('token', $botToken)
        ->constructorParameter('allowedUpdates', $allowedUpdates),
    Nutgram::class => factory(static fn (NutgramFactory $factory): Nutgram => $factory->create()),
    TelegramWebhookAction::class => autowire()->constructorParameter('secretToken', $webhookSecret),
    TelegramWebhookCommand::class => autowire()
        ->constructorParameter('secretToken', $webhookSecret)
        ->constructorParameter('defaultUrl', $webhookUrl)
        ->constructorParameter('allowedUpdates', $allowedUpdates),
];
