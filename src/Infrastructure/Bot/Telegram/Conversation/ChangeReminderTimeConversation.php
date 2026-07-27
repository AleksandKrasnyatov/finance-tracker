<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Conversation;

use App\Application\Gateway\TranslatorInterface;
use App\Application\UseCase\User\Command\ChangeReminderTimeCommand;
use App\Application\UseCase\User\Command\ChangeReminderTimeHandler;
use App\Domain\Enum\Locale;
use App\Infrastructure\Bot\Telegram\Handler\Reminder\RemindersHandler;
use App\Infrastructure\Bot\Telegram\TelegramUserData;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

final class ChangeReminderTimeConversation extends Conversation
{
    public function __construct(
        private readonly ChangeReminderTimeHandler $changeTime,
        private readonly RemindersHandler $reminders,
        private readonly TelegramUserData $userData,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function start(Nutgram $bot): void
    {
        $bot->sendMessage($this->translator->trans(
            'bot.reminders.enterTime',
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
        $text = ConversationText::fromMessage($bot);
        if (ConversationText::isCommandOrEmpty($text)) {
            $bot->sendMessage($this->translator->trans(
                'bot.reminders.enterTimeText',
                locale: $locale,
            ));
            $this->next('save');

            return;
        }

        $context = $this->userData->getOrSet($bot);

        $this->changeTime->handle(new ChangeReminderTimeCommand(
            $context['userId'],
            $text,
        ));

        $bot->sendMessage($this->translator->trans(
            'bot.reminders.timeChanged',
            ['%time%' => $text],
            $locale,
        ));
        $this->end();
        $this->reminders->list($bot);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function locale(Nutgram $bot): Locale
    {
        return $this->userData->getOrSet($bot)['locale'];
    }
}
