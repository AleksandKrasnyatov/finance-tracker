<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler;

use App\Application\UseCase\Account\Query\GetAccountBalanceHandler as Handler;
use App\Application\UseCase\Account\Query\GetAccountBalanceQuery;
use App\Infrastructure\Bot\Telegram\Formatter\BalanceMessageFormatter;
use App\Infrastructure\Bot\Telegram\TelegramUserData;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Nutgram;
use UnexpectedValueException;

final readonly class BalanceHandler
{
    public function __construct(
        private Handler $handler,
        private TelegramUserData $userData,
        private BalanceMessageFormatter $formatter,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function __invoke(Nutgram $bot): void
    {
        if ($bot->userId() === null) {
            throw new UnexpectedValueException('Telegram user is missing from the update.');
        }

        $context = $this->userData->getOrSet($bot);

        $result = $this->handler->handle(new GetAccountBalanceQuery(
            $context['userId'],
            $context['accountId'],
        ));

        $bot->sendMessage($this->formatter->format($result, $context['locale']));
    }
}
