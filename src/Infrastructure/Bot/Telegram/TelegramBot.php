<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram;

use App\Application\Gateway\TranslatorInterface;
use App\Domain\Enum\Locale;
use App\Infrastructure\Bot\Telegram\Conversation\AddCategoryConversation;
use App\Infrastructure\Bot\Telegram\Handler\AddTransactionHandler;
use App\Infrastructure\Bot\Telegram\Handler\ExceptionHandler;
use App\Infrastructure\Bot\Telegram\Handler\StartHandler;
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

        $this->bot->onCommand('start', StartHandler::class);
        $this->bot->onCommand('category', AddCategoryConversation::class);

        foreach ([Locale::En, Locale::Ru] as $locale) {
            $this->bot->onText(
                $this->translator->trans('bot.button.add_category', locale: $locale),
                AddCategoryConversation::class,
            );
        }

        $this->bot->onText('{sign}{amount} {category} {description}', AddTransactionHandler::class)
            ->where('sign', '[+-]')
            ->where('amount', '\d+(?:[.,]\d{1,2})?')
            ->where('category', '\S+')
            ->where('description', '.+');
        $this->bot->onText('{sign}{amount} {category}', AddTransactionHandler::class)
            ->where('sign', '[+-]')
            ->where('amount', '\d+(?:[.,]\d{1,2})?')
            ->where('category', '\S+');

        $this->bot->onException(ExceptionHandler::class);

        $this->configured = true;
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
}
