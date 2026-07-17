<?php

declare(strict_types=1);

namespace App\Infrastructure\Translation;

use App\Application\Gateway\TranslatorInterface;
use App\Domain\Enum\Locale;
use Symfony\Component\Translation\Translator;

final readonly class SymfonyTranslator implements TranslatorInterface
{
    public function __construct(
        private Translator $translator,
    ) {
    }

    public function trans(string $id, array $parameters = [], ?Locale $locale = null): string
    {
        return $this->translator->trans(
            $id,
            $parameters,
            locale: $locale?->value,
        );
    }
}
