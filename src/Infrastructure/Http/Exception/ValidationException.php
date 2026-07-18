<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Exception;

use DomainException;

final class ValidationException extends DomainException
{
    /**
     * @param array<string, string> $errors
     */
    public function __construct(
        public readonly array $errors,
        string $message = 'Validation failed',
    ) {
        parent::__construct($message);
    }
}
