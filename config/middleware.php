<?php

declare(strict_types=1);

use App\Infrastructure\Http\Middleware\ValidationExceptionHandler;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\App;

return static function (App $app): void {
    $app->add(ValidationExceptionHandler::class);
    $app->addBodyParsingMiddleware();

    /** @var ContainerInterface $container */
    $container = $app->getContainer();

    $displayErrorDetails = (getenv('APP_ENV') ?: 'prod') === 'dev';
    /** @var LoggerInterface $logger */
    $logger = $container->get(LoggerInterface::class);
    $app->addErrorMiddleware($displayErrorDetails, true, true, $logger);
};
