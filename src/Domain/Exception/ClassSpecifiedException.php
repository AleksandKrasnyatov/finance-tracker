<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException;
use Throwable;
use Webmozart\Assert\Assert;

/**
 * @template T
 */
abstract class ClassSpecifiedException extends DomainException
{
    /**
     * @param  class-string<T> $entityClass
     */
    public function __construct(
        public readonly string $entityClass,
        string $message = "",
        int $code = 0,
        ?Throwable $previous = null
    ) {
        Assert::classExists($entityClass);
        parent::__construct($message, $code, $previous);
    }

    public function getEntityName(): string
    {
        return str_replace('\\', '/', $this->entityClass)
                |> basename(...);
    }
}
