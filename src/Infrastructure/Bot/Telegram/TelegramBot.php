<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram;

use App\Application\Gateway\TranslatorInterface;
use App\Infrastructure\Bot\Telegram\Handler\AddTransactionHandler;
use App\Infrastructure\Bot\Telegram\Handler\BalanceHandler;
use App\Infrastructure\Bot\Telegram\Handler\Category\CategoriesListHandler;
use App\Infrastructure\Bot\Telegram\Handler\Category\CategoryCallback;
use App\Infrastructure\Bot\Telegram\Handler\ExceptionHandler;
use App\Infrastructure\Bot\Telegram\Handler\Reminder\ReminderCallback;
use App\Infrastructure\Bot\Telegram\Handler\Reminder\RemindersHandler;
use App\Infrastructure\Bot\Telegram\Handler\ResetHandler;
use App\Infrastructure\Bot\Telegram\Handler\Settings\SettingsCallback;
use App\Infrastructure\Bot\Telegram\Handler\Settings\SettingsHandler;
use App\Infrastructure\Bot\Telegram\Handler\StartHandler;
use App\Infrastructure\Bot\Telegram\Handler\Transaction\TransactionCallback;
use App\Infrastructure\Bot\Telegram\Handler\Transaction\TransactionsListHandler;
use App\Domain\Enum\Locale;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\RunningMode;

final class TelegramBot
{
    private bool $configured = false;

    public function __construct(
        private readonly Nutgram $bot,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function configure(): void
    {
        if ($this->configured) {
            return;
        }

        Conversation::refreshOnDeserialize();

        $this->bot->onCommand('start', StartHandler::class)
            ->description($this->commandDescriptions('bot.command.start'));
        $this->bot->onCommand('reset', ResetHandler::class)
            ->description($this->commandDescriptions('bot.command.reset'));
        $this->bot->onCommand('category', CategoriesListHandler::class)
            ->description($this->commandDescriptions('bot.command.category'));
        $this->bot->onCommand('balance', BalanceHandler::class)
            ->description($this->commandDescriptions('bot.command.balance'));
        $this->bot->onCommand('settings', SettingsHandler::class)
            ->description($this->commandDescriptions('bot.command.settings'));
        $this->bot->onCommand('reminders', RemindersHandler::class)
            ->description($this->commandDescriptions('bot.command.reminders'));
        $this->bot->onCommand('transactions', TransactionsListHandler::class)
            ->description($this->commandDescriptions('bot.command.transactions'));

        CategoryCallback::register($this->bot);
        SettingsCallback::register($this->bot);
        ReminderCallback::register($this->bot);
        TransactionCallback::register($this->bot);
        AddTransactionHandler::register($this->bot);

        $this->bot->onException(ExceptionHandler::class);

        $this->configured = true;
    }

    public function syncCommandMenu(): void
    {
        $this->configure();
        $this->bot->registerMyCommands();
        $this->syncDescription();
    }

    private function syncDescription(): void
    {
        foreach ([Locale::En, Locale::Ru] as $locale) {
            $this->bot->setMyDescription(
                description: $this->translator->trans('bot.description', locale: $locale),
                language_code: $locale->value,
            );
        }

        $this->bot->setMyDescription(
            description: $this->translator->trans('bot.description', locale: Locale::default()),
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function run(RunningMode $runningMode): void
    {
        $this->configure();
        $this->bot->setRunningMode($runningMode);
        $this->bot->run();
    }

    /**
     * @return array<string, string>
     */
    private function commandDescriptions(string $key): array
    {
        $descriptions = [];
        foreach ([Locale::En, Locale::Ru] as $locale) {
            $descriptions[$locale->value] = $this->translator->trans($key, locale: $locale);
        }

        return $descriptions;
    }
}
