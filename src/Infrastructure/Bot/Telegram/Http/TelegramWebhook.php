<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Http;

use SergiX44\Nutgram\RunningMode\Webhook;

final class TelegramWebhook extends Webhook
{
    public function __construct(
        private readonly string $payload,
        string $providedSecretToken,
        string $expectedSecretToken,
    ) {
        parent::__construct(
            static fn (): string => $providedSecretToken,
            $expectedSecretToken,
        );

        $this->setSafeMode(true);
    }

    protected function input(): ?string
    {
        return $this->payload !== '' ? $this->payload : null;
    }
}
