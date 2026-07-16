<?php

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return [
    LoggerInterface::class => static function (ContainerInterface $container) {
        /**
         * @psalm-suppress MixedArrayAccess
         * @var array{
         *     debug:bool,
         *     stderr:bool,
         *     file:string|null,
         *     processors:string[]
         * } $config
         */
        $config = $container->get('config')['logger'];

        $level = $config['debug'] ? Level::Debug : Level::Info;

        $log = new Logger('API');

        if ($config['stderr']) {
            $log->pushHandler(new StreamHandler('php://stderr', $level));
        }

        if ($config['file'] !== null) {
            $log->pushHandler(new StreamHandler($config['file'], $level));
        }

        return $log;
    },

    'config' => [
        'logger' => [
            'debug' => (bool) (getenv('APP_DEBUG') ?: 0),
            'file' => null,
            'stderr' => true,
        ],
    ],
];
