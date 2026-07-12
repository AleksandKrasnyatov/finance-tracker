<?php

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

$env = getenv('APP_ENV') ?: 'prod';
$levelName = strtolower(getenv('LOG_LEVEL') ?: ($env === 'dev' ? 'debug' : 'info'));
$level = Level::fromName($levelName);

return [
    LoggerInterface::class => static function () use ($level,): LoggerInterface {
        $logPath = __DIR__ . '/../../var/log/app.log';
        $logger = new Logger('app');
        $logger->pushHandler(new StreamHandler($logPath, $level));
        $logger->pushHandler(new StreamHandler('php://stderr', $level));

        return $logger;
    },
];
