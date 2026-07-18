<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler;

use App\Application\Gateway\TranslatorInterface;
use App\Application\UseCase\Account\Transaction\AddTransactionCommand;
use App\Application\UseCase\Account\Transaction\AddTransactionHandler as Handler;
use App\Domain\Enum\Locale;
use App\Domain\Enum\TransactionType;
use App\Infrastructure\Bot\Telegram\TelegramUserData;
use DomainException;
use InvalidArgumentException;
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

        $type = $this->resolveType($sign);
        $amount = str_replace(',', '.', $amount);
        $comment = trim((string)$description);
        $context = $this->userData->getOrSet($bot);
        $locale = $context['locale'];

        try {
            $this->handler->handle(new AddTransactionCommand(
                $context['userId'],
                $context['accountId'],
                $type->value,
                $amount,
                $category,
                $comment,
            ));
        } catch (DomainException | InvalidArgumentException $exception) {
            $bot->sendMessage($this->userMessage($exception, $locale));
            return;
        }

        $message = $this->translator->trans('bot.transaction.recorded', [
            '%sign%' => $sign,
            '%amount%' => $amount,
            '%category%' => mb_strtolower($category),
            '%type%' => $this->translator->trans($type->value, locale: $locale),
            '%comment%' => !empty($comment) ? " ($comment)" : '',
        ], $locale);

        $bot->sendMessage($message);
    }

    private function resolveType(string $sign): TransactionType
    {
        return match ($sign) {
            '+' => TransactionType::Income,
            '-' => TransactionType::Expense,
            default => throw new UnexpectedValueException('Transaction sign must be + or -.'),
        };
    }

    /**
     * todo нужно разобраться с обработкой исключений
     */
    private function userMessage(DomainException|InvalidArgumentException $exception, Locale $locale): string
    {
        $key = match ($exception->getMessage()) {
            'Category is not found.' => 'error.category_not_found',
            'The user has no account.', 'Please run /start first.' => 'error.start_required',
            default => null,
        };

        return $key === null
            ? $exception->getMessage()
            : $this->translator->trans($key, locale: $locale);
    }
}
