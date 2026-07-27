<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Conversation;

use App\Application\Gateway\TranslatorInterface;
use App\Application\UseCase\Account\Command\Transaction\ChangeTransactionDateCommand;
use App\Application\UseCase\Account\Command\Transaction\ChangeTransactionDateHandler;
use App\Domain\Enum\Locale;
use App\Infrastructure\Bot\Telegram\Handler\Transaction\TransactionViewHandler;
use App\Infrastructure\Bot\Telegram\TelegramUserData;
use DateMalformedStringException;
use DateTimeImmutable;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

final class ChangeTransactionDateConversation extends Conversation
{
    public string $transactionId;

    public function __construct(
        private readonly ChangeTransactionDateHandler $changeDate,
        private readonly TransactionViewHandler $view,
        private readonly TelegramUserData $userData,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function start(Nutgram $bot, string $transactionId): void
    {
        $this->transactionId = $transactionId;
        $bot->sendMessage($this->translator->trans(
            'bot.transactions.enterDate',
            locale: $this->locale($bot),
        ));
        $this->next('save');
    }

    /**
     * @throws InvalidArgumentException|DateMalformedStringException
     */
    public function save(Nutgram $bot): void
    {
        $locale = $this->locale($bot);
        $text = ConversationText::fromMessage($bot);
        if (!$this->validateDate($text)) {
            $bot->sendMessage($this->translator->trans(
                'bot.transactions.dateInvalid',
                locale: $locale,
            ));
            $this->next('save');

            return;
        }

        $context = $this->userData->getOrSet($bot);
        $this->changeDate->handle(new ChangeTransactionDateCommand(
            $context['userId'],
            $context['accountId'],
            $this->transactionId,
            $text
        ));

        $bot->sendMessage($this->translator->trans(
            'bot.transactions.dateChanged',
            ['%date%' => $text],
            $locale,
        ));
        $this->end();
        ($this->view)($bot, $this->transactionId);
    }

    private function validateDate(string $text): bool
    {
        if (ConversationText::isCommandOrEmpty($text)) {
            return false;
        }

        if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $text, $matches) !== 1) {
            return false;
        }

        return true;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function locale(Nutgram $bot): Locale
    {
        return $this->userData->getOrSet($bot)['locale'];
    }
}
