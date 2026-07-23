<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Application\UseCase\Reminder\Command\SendDailyRemindersHandler;
use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeZone;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SendDailyRemindersCommand extends Command
{
    public function __construct(
        private readonly SendDailyRemindersHandler $handler,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('reminders:send')
            ->setDescription('Send daily reminders to users who have not recorded transactions today');
    }

    /**
     * @throws DateMalformedStringException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $nowUtc = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        $output->writeln(sprintf(
            '<info>reminders:send</info> at %s UTC',
            $nowUtc->format('Y-m-d H:i:s'),
        ));

        $this->handler->handle($nowUtc);

        return Command::SUCCESS;
    }
}
