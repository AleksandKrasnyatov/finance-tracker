<?php

declare(strict_types=1);

namespace Test\Unit\Infrastructure\Http\Exception;

use App\Infrastructure\Http\Exception\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ValidationExceptionTest extends TestCase
{
    #[Test]
    public function givenMessageAndErrorsWhenValidationExceptionIsCreatedThenMessageAndErrorsAreAccessible(): void
    {
        $exception = new ValidationException(
            ['body' => 'Body must be JSON-encoded'],
            'Incorrect request',
        );

        self::assertSame('Incorrect request', $exception->getMessage());
        self::assertSame(
            ['body' => 'Body must be JSON-encoded'],
            $exception->errors
        );
    }
}
