<?php

declare(strict_types=1);

use App\Infrastructure\Http\Middleware\ValidationExceptionHandler;
use Slim\App;

return static function (App $app): void {
    $app->add(ValidationExceptionHandler::class);
    $app->addBodyParsingMiddleware();
    $app->addErrorMiddleware(true, true, true);
};
