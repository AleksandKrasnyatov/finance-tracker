<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler;

use App\Application\Gateway\TranslatorInterface;
use App\Application\UseCase\Account\Transaction\AddTransactionCommand;
use App\Application\UseCase\Account\Transaction\AddTransactionHandler as Handler;
use App\Domain\Enum\TransactionType;
use App\Infrastructure\Bot\Telegram\TelegramUserData;
use DateMalformedStringException;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Nutgram;
use UnexpectedValueException;

final readonly class AddTransactionHandler
{
    public function __construct(
        private Handler $handler,
        private TelegramUserData $userData,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws DateMalformedStringException
     * @throws InvalidArgumentException
     */
    public function __invoke(
        Nutgram $bot,
        string $sign,
        string $amount,
        string $category,
        ?string $description = null,
    ): void {
        if ($bot->userId() === null) {
            throw new UnexpectedValueException('Telegram user is missing from the update.');
        }

        $type = TransactionType::fromSign($sign);
        $amount = str_replace(',', '.', $amount);
        $comment = trim((string)$description);
        $context = $this->userData->getOrSet($bot);
        $locale = $context['locale'];

        $this->handler->handle(new AddTransactionCommand(
            $context['userId'],
            $context['accountId'],
            $type->value,
            $amount,
            $category,
            $comment,
        ));

        $message = $this->translator->trans('bot.transaction.recorded', [
            '%sign%' => $sign,
            '%amount%' => $amount,
            '%category%' => mb_strtolower($category),
            '%type%' => $this->translator->trans($type->value, locale: $locale),
            '%comment%' => !empty($comment) ? " ($comment)" : '',
        ], $locale);

        $bot->sendMessage($message);
    }
}
