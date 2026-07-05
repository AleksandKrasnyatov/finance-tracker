<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use App\Infrastructure\Http\Exception\ValidationException;
use App\Infrastructure\Http\Response\JsonResponse;
use Override;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ValidationExceptionHandler implements MiddlewareInterface
{
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ValidationException $exception) {
            return new JsonResponse(
                [
                    'message' => $exception->getMessage(),
                    'errors' => $exception->errors,
                ],
                422
            );
        }
    }
}
