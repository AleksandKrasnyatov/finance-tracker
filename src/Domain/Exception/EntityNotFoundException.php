<?php

declare(strict_types=1);

namespace App\Domain\Exception;

/**
 * @template T of object
 * @extends ClassSpecifiedException<T>
 */
final class EntityNotFoundException extends ClassSpecifiedException
{
}
