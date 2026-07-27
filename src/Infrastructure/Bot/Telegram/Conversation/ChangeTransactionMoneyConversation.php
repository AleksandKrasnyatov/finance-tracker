<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Conversation;

use App\Application\Gateway\TranslatorInterface;
use App\Application\UseCase\Account\Command\Transaction\ChangeTransactionMoneyCommand;
use App\Application\UseCase\Account\Command\Transaction\ChangeTransactionMoneyHandler;
use App\Domain\Enum\Locale;
use App\Infrastructure\Bot\Telegram\Handler\Transaction\TransactionViewHandler;
use App\Infrastructure\Bot\Telegram\TelegramUserData;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

final class ChangeTransactionMoneyConversation extends Conversation
{
    public string $transactionId;

    public function __construct(
        private readonly ChangeTransactionMoneyHandler $changeMoney,
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
            'bot.transactions.enterAmount',
            locale: $this->locale($bot),
        ));
        $this->next('save');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function save(Nutgram $bot): void
    {
        $locale = $this->locale($bot);
        $amount = str_replace(',', '.', ConversationText::fromMessage($bot));
        if (ConversationText::isCommandOrEmpty($amount) || !is_numeric($amount)) {
            $bot->sendMessage($this->translator->trans(
                'bot.transactions.enterAmountText',
                locale: $locale,
            ));
            $this->next('save');

            return;
        }

        $context = $this->userData->getOrSet($bot);
        $this->changeMoney->handle(new ChangeTransactionMoneyCommand(
            $context['userId'],
            $context['accountId'],
            $this->transactionId,
            $amount,
        ));

        $bot->sendMessage($this->translator->trans(
            'bot.transactions.amountChanged',
            ['%amount%' => number_format((float)$amount, thousands_separator: ' ')],
            $locale,
        ));
        $this->end();
        ($this->view)($bot, $this->transactionId);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function locale(Nutgram $bot): Locale
    {
        return $this->userData->getOrSet($bot)['locale'];
    }
}
