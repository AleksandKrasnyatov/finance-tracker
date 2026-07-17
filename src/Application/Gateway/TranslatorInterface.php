<?php

declare(strict_types=1);

namespace App\Application\Gateway;

use App\Domain\Enum\Locale;

interface TranslatorInterface
{
    /**
     * @param array<string, string|int|float> $parameters
     */
    public function trans(string $id, array $parameters = [], ?Locale $locale = null): string;
}
