<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Console;

use App\Infrastructure\Bot\Telegram\TelegramBotFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use SergiX44\Nutgram\RunningMode\Polling;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RunTelegramBotCommand extends Command
{
    public function __construct(
        private readonly TelegramBotFactory $telegramBotFactory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('telegram:run')
            ->setDescription('Run the Telegram bot using long polling.');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $telegramBot = $this->telegramBotFactory->create();
        $output->writeln('<info>Telegram bot is running.</info>');
        $telegramBot->run(new Polling());

        return Command::SUCCESS;
    }
}
