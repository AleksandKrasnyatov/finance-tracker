<?php

declare(strict_types=1);

namespace App\Infrastructure\Gateway;

use App\Application\Gateway\Notification;
use App\Application\Gateway\NotifierInterface;
use App\Application\Gateway\TranslatorInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Id;
use DomainException;
use SergiX44\Nutgram\Nutgram;

final readonly class TelegramNotifier implements NotifierInterface
{
    public function __construct(
        private Nutgram $bot,
        private UserRepositoryInterface $users,
        private TranslatorInterface $translator,
    ) {
    }

    public function notify(Id $userId, Notification $notification): void
    {
        $user = $this->users->get($userId);

        if ($user->telegramId === null) {
            throw new DomainException('User has no telegram id.');
        }

        $this->bot->sendMessage(
            text: $this->translator->trans(
                $notification->type,
                $notification->parameters,
                $user->locale,
            ),
            chat_id: $user->telegramId->value,
        );
    }
}
