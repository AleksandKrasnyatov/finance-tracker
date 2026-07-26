<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Conversation;

use App\Application\Gateway\TranslatorInterface;
use App\Application\UseCase\Account\Command\Transaction\ChangeTransactionDescriptionCommand;
use App\Application\UseCase\Account\Command\Transaction\ChangeTransactionDescriptionHandler;
use App\Domain\Enum\Locale;
use App\Infrastructure\Bot\Telegram\Handler\Transaction\TransactionViewHandler;
use App\Infrastructure\Bot\Telegram\TelegramUserData;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

final class ChangeTransactionDescriptionConversation extends Conversation
{
    public string $transactionId;

    public function __construct(
        private readonly ChangeTransactionDescriptionHandler $changeDescription,
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
            'bot.transactions.enterDescription',
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
        $text = trim((string)$bot->message()?->text);
        if (str_starts_with($text, '/')) {
            $bot->sendMessage($this->translator->trans(
                'bot.transactions.enterDescriptionText',
                locale: $locale,
            ));
            $this->next('save');

            return;
        }

        $context = $this->userData->getOrSet($bot);
        $this->changeDescription->handle(new ChangeTransactionDescriptionCommand(
            $context['userId'],
            $context['accountId'],
            $this->transactionId,
            $text,
        ));

        $bot->sendMessage($this->translator->trans(
            'bot.transactions.descriptionChanged',
            locale: $locale,
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
