<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler\Reminder;

use App\Application\Gateway\TranslatorInterface;
use App\Application\UseCase\User\Command\ChangeRemindersEnabledCommand;
use App\Application\UseCase\User\Command\ChangeRemindersEnabledHandler;
use App\Application\UseCase\User\Command\ChangeReminderTimezoneCommand;
use App\Application\UseCase\User\Command\ChangeReminderTimezoneHandler;
use App\Domain\Entity\Reminder;
use App\Domain\Enum\Locale;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Timezone;
use App\Infrastructure\Bot\Telegram\CallbackData;
use App\Infrastructure\Bot\Telegram\Conversation\ChangeReminderTimeConversation;
use App\Infrastructure\Bot\Telegram\Handler\Settings\SettingsCallback;
use App\Infrastructure\Bot\Telegram\TelegramScreen;
use App\Infrastructure\Bot\Telegram\TelegramUserData;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

final readonly class RemindersHandler
{
    public function __construct(
        private TelegramUserData $userData,
        private UserRepositoryInterface $users,
        private ChangeRemindersEnabledHandler $changeEnabled,
        private ChangeReminderTimezoneHandler $changeTimezone,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function __invoke(Nutgram $bot): void
    {
        $this->list($bot);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function list(Nutgram $bot): void
    {
        $context = $this->userData->getOrSet($bot);
        $locale = $context['locale'];
        $reminder = $this->reminder($context['userId']);

        TelegramScreen::render(
            $bot,
            $this->translator->trans('bot.reminders.body', locale: $locale),
            $this->listMarkup($reminder, $locale),
            ParseMode::HTML,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function enable(Nutgram $bot): void
    {
        $this->setEnabled($bot, true);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function disable(Nutgram $bot): void
    {
        $this->setEnabled($bot, false);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function askTime(Nutgram $bot): void
    {
        TelegramScreen::ensureUser($bot);
        $bot->answerCallbackQuery();
        ChangeReminderTimeConversation::begin($bot);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function timezones(Nutgram $bot): void
    {
        $context = $this->userData->getOrSet($bot);
        $locale = $context['locale'];
        $currentTimezone = $this->reminder($context['userId'])->timezone;

        $markup = InlineKeyboardMarkup::make();
        foreach (Timezone::common() as $index => $timezone) {
            $prefix = $timezone->value === $currentTimezone->value ? '✓ ' : '';
            $markup->addRow(InlineKeyboardButton::make(
                $prefix . $timezone->value,
                callback_data: CallbackData::data(ReminderCallback::SET_TIMEZONE, (string)$index),
            ));
        }
        $markup->addRow(InlineKeyboardButton::make(
            $this->translator->trans('bot.reminders.back', locale: $locale),
            callback_data: ReminderCallback::LIST,
        ));

        TelegramScreen::render(
            $bot,
            $this->translator->trans('bot.reminders.timezoneTitle', locale: $locale),
            $markup,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setTimezone(Nutgram $bot, string $index): void
    {
        $zones = Timezone::common();
        $timezone = $zones[(int)$index] ?? null;
        if ($timezone === null) {
            $bot->answerCallbackQuery(
                text: $this->translator->trans(
                    'bot.reminders.timezoneInvalid',
                    locale: $this->userData->getOrSet($bot)['locale'],
                ),
                show_alert: true,
            );

            return;
        }

        $context = $this->userData->getOrSet($bot);
        $this->changeTimezone->handle(new ChangeReminderTimezoneCommand(
            $context['userId'],
            $timezone->value,
        ));

        $this->list($bot);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function setEnabled(Nutgram $bot, bool $enabled): void
    {
        $context = $this->userData->getOrSet($bot);
        $reminder = $this->reminder($context['userId']);

        if ($reminder->remindersEnabled !== $enabled) {
            $this->changeEnabled->handle(
                new ChangeRemindersEnabledCommand(
                    $context['userId'],
                    $enabled,
                )
            );
        }

        $this->list($bot);
    }

    private function reminder(string $userId): Reminder
    {
        return $this->users->get(new Id($userId))->reminder;
    }

    private function listMarkup(Reminder $reminder, Locale $locale): InlineKeyboardMarkup
    {
        $onPrefix = $reminder->remindersEnabled ? '🟢 ' : '⚪ ';
        $offPrefix = $reminder->remindersEnabled ? '⚪ ' : '🔴 ';

        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(
                    $onPrefix . $this->translator->trans('bot.reminders.on', locale: $locale),
                    callback_data: ReminderCallback::ON,
                ),
                InlineKeyboardButton::make(
                    $offPrefix . $this->translator->trans('bot.reminders.off', locale: $locale),
                    callback_data: ReminderCallback::OFF,
                ),
            )
            ->addRow(InlineKeyboardButton::make(
                $this->translator->trans('bot.reminders.time', [
                    '%time%' => $reminder->reminderTime->value,
                ], $locale),
                callback_data: ReminderCallback::TIME,
            ))
            ->addRow(InlineKeyboardButton::make(
                $this->translator->trans('bot.reminders.timezone', locale: $locale),
                callback_data: ReminderCallback::TIMEZONE,
            ))
            ->addRow(InlineKeyboardButton::make(
                $this->translator->trans('bot.reminders.back', locale: $locale),
                callback_data: SettingsCallback::LIST,
            ));
    }
}
