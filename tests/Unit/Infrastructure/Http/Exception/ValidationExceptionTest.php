<?php

declare(strict_types=1);

namespace Test\Unit\Infrastructure\Http\Exception;

use App\Infrastructure\Http\Exception\ValidationException;
use PHPUnit\Framework\TestCase;

class ValidationExceptionTest extends TestCase
{
    public function testSuccess(): void
    {
        $exception = new ValidationException(
            ['body' => 'Тело запроса должно быть JSON-объектом'],
            'Некорректный запрос',
        );

        self::assertSame('Некорректный запрос', $exception->getMessage());
        self::assertSame(
            ['body' => 'Тело запроса должно быть JSON-объектом'],
            $exception->errors
        );
    }
}
