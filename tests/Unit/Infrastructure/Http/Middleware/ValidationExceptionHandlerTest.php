<?php

declare(strict_types=1);

namespace Test\Unit\Infrastructure\Http\Middleware;

use App\Infrastructure\Http\Exception\ValidationException;
use App\Infrastructure\Http\Middleware\ValidationExceptionHandler;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

final class ValidationExceptionHandlerTest extends TestCase
{
    #[Test]
    public function givenHandlerWithoutExceptionWhenMiddlewareProcessesRequestThenOriginalResponseIsReturned(): void
    {
        $handler = self::createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($source = self::createResponse());

        $response = new ValidationExceptionHandler()->process(self::createRequest(), $handler);

        self::assertSame($source, $response);
    }

    #[Test]
    public function givenHandlerThrowsValidationExceptionWhenMiddlewareProcessesRequestThenUnprocessableEntityJsonIsReturned(): void
    {
        $middleware = new ValidationExceptionHandler();

        $errors = ['name' => 'Name cannot be empty'];

        $handler = self::createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willThrowException(new ValidationException($errors));

        $response = $middleware->process(self::createRequest(), $handler);

        self::assertSame(422, $response->getStatusCode());
        self::assertJson($body = (string) $response->getBody());
        self::assertSame(
            [
                'message' => 'Validation failed',
                'errors' => $errors,
            ],
            json_decode($body, true)
        );
    }

    private static function createRequest(): ServerRequestInterface
    {
        return new ServerRequestFactory()->createServerRequest('POST', 'http://test');
    }

    private static function createResponse(): ResponseInterface
    {
        return new ResponseFactory()->createResponse();
    }
}
