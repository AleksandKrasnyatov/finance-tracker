<?php

declare(strict_types=1);

use App\Infrastructure\Http\Action\HomeAction;
use App\Infrastructure\Bot\Telegram\Http\TelegramWebhookAction;
use Slim\App;

return static function (App $app): void {
    $app->get('/', HomeAction::class);
    $app->post('/telegram/webhook', TelegramWebhookAction::class);
};
