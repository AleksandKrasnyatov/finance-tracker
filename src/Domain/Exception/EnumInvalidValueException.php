<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use UnitEnum;

/**
 * @template T of UnitEnum
 * @extends ClassSpecifiedException<T>
 */
final class EnumInvalidValueException extends ClassSpecifiedException
{
}
