<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Handler\Settings;

use App\Application\Gateway\TranslatorInterface;
use App\Application\UseCase\User\Command\ChangeUserLocaleCommand;
use App\Application\UseCase\User\Command\ChangeUserLocaleHandler;
use App\Domain\Enum\Locale;
use App\Infrastructure\Bot\Telegram\Handler\Reminder\ReminderCallback;
use App\Infrastructure\Bot\Telegram\TelegramScreen;
use App\Infrastructure\Bot\Telegram\TelegramUserData;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

final readonly class SettingsHandler
{
    public function __construct(
        private TelegramUserData $userData,
        private ChangeUserLocaleHandler $changeLocale,
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
        TelegramScreen::ensureUser($bot);
        $locale = $this->userData->getOrSet($bot)['locale'];

        $markup = InlineKeyboardMarkup::make()
            ->addRow(InlineKeyboardButton::make(
                $this->translator->trans('bot.settings.language', locale: $locale),
                callback_data: SettingsCallback::LANGUAGE,
            ))
            ->addRow(InlineKeyboardButton::make(
                $this->translator->trans('bot.settings.notifications', locale: $locale),
                callback_data: ReminderCallback::LIST,
            ));

        TelegramScreen::render(
            $bot,
            $this->translator->trans('bot.settings.title', locale: $locale),
            $markup,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function language(Nutgram $bot): void
    {
        TelegramScreen::ensureUser($bot);
        $locale = $this->userData->getOrSet($bot)['locale'];

        TelegramScreen::render(
            $bot,
            $this->translator->trans('bot.settings.languageTitle', locale: $locale),
            $this->languageMarkup($locale),
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setLanguage(Nutgram $bot, string $locale): void
    {
        TelegramScreen::ensureUser($bot);
        $context = $this->userData->getOrSet($bot);
        $newLocale = Locale::fromLanguageCode($locale);

        if ($context['locale'] !== $newLocale) {
            $this->changeLocale->handle(new ChangeUserLocaleCommand(
                $context['userId'],
                $newLocale->value,
            ));
            $context = $this->userData->refresh($bot);
        }

        $bot->answerCallbackQuery(
            text: $this->translator->trans('bot.settings.languageChanged', locale: $context['locale']),
        );
        $bot->editMessageText(
            text: $this->translator->trans('bot.settings.languageTitle', locale: $context['locale']),
            reply_markup: $this->languageMarkup($context['locale']),
        );
    }

    private function languageMarkup(Locale $current): InlineKeyboardMarkup
    {
        $markup = InlineKeyboardMarkup::make();

        foreach (Locale::list() as $code => $label) {
            $prefix = $current->value === $code ? '✓ ' : '';
            $markup->addRow(InlineKeyboardButton::make(
                $prefix . $label,
                callback_data: SettingsCallback::data(SettingsCallback::SET_LANGUAGE, $code),
            ));
        }

        $markup->addRow(InlineKeyboardButton::make(
            $this->translator->trans('bot.settings.back', locale: $current),
            callback_data: SettingsCallback::LIST,
        ));

        return $markup;
    }
}
