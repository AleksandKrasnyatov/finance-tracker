<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Console;

use App\Infrastructure\Bot\Telegram\TelegramBot;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use RuntimeException;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Exceptions\TelegramException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class TelegramWebhookCommand extends Command
{
    /**
     * @param list<string> $allowedUpdates
     */
    public function __construct(
        private readonly Nutgram $bot,
        private readonly TelegramBot $telegramBot,
        private readonly string $secretToken,
        private readonly string $defaultUrl,
        private readonly array $allowedUpdates,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('telegram:webhook')
            ->setDescription('Register or remove the Telegram webhook.')
            ->addArgument('url', InputArgument::OPTIONAL, 'Public HTTPS webhook URL')
            ->addOption('delete', null, InputOption::VALUE_NONE, 'Delete the current webhook')
            ->addOption(
                'drop-pending-updates',
                null,
                InputOption::VALUE_NONE,
                'Discard updates waiting at Telegram',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dropPendingUpdates = (bool) $input->getOption('drop-pending-updates');

        if ($input->getOption('delete')) {
            $this->bot->deleteWebhook(drop_pending_updates: $dropPendingUpdates);
            $output->writeln('<info>Webhook deleted.</info>');

            return Command::SUCCESS;
        }

        $url = $input->getArgument('url') ?: $this->defaultUrl;
        if (!is_string($url) || $url === '') {
            throw new RuntimeException('Webhook URL is required. Pass it as an argument or set TELEGRAM_WEBHOOK_URL.');
        }

        $parsedUrl = parse_url($url);
        if (
            $parsedUrl === false
            || ($parsedUrl['scheme'] ?? null) !== 'https'
            || !is_string($parsedUrl['host'] ?? null)
        ) {
            throw new RuntimeException('Webhook URL must be a valid HTTPS URL.');
        }

        if ($this->secretToken === '') {
            throw new RuntimeException('TELEGRAM_WEBHOOK_SECRET is not configured.');
        }

        $this->bot->setWebhook(
            url: $url,
            allowed_updates: $this->allowedUpdates,
            drop_pending_updates: $dropPendingUpdates,
            secret_token: $this->secretToken,
        );
        $this->telegramBot->syncCommandMenu();

        $output->writeln('<info>Webhook set:</info> ' . $url);
        $output->writeln('<info>Telegram command menu updated.</info>');
        $this->writeWebhookInfo($output);

        return Command::SUCCESS;
    }

    /**
     * @throws TelegramException
     * @throws GuzzleException
     * @throws JsonException
     */
    private function writeWebhookInfo(OutputInterface $output): void
    {
        $info = $this->bot->getWebhookInfo();
        if ($info === null) {
            return;
        }

        $output->writeln('<comment>url:</comment> ' . ($info->url !== '' ? $info->url : '(empty)'));
        $output->writeln('<comment>pending_update_count:</comment> ' . (string) $info->pending_update_count);
        if ($info->last_error_message !== null) {
            $output->writeln('<error>last_error_message:</error> ' . $info->last_error_message);
        }
    }
}
