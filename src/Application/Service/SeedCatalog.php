<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Gateway\TranslatorInterface;
use App\Domain\Dto\CategoryDto;
use App\Domain\Entity\Category;
use App\Domain\Enum\Locale;

final readonly class SeedCatalog
{
    public const string ACCOUNT_CODE = 'main';

    private const string PREFIX = 'catalog.';

    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function accountName(Locale $locale): string
    {
        return $this->localize(self::ACCOUNT_CODE, $locale);
    }

    /**
     * @return list<CategoryDto>
     */
    public function categories(Locale $locale): array
    {
        $categories = [];

        foreach (Category::defaults() as $category) {
            $code = $category->code;
            $categories[] = new CategoryDto(
                $category->type,
                $this->localize($code, $locale),
                $code,
            );
        }

        return $categories;
    }

    public function localize(string $code, Locale $locale): string
    {
        return $this->translator->trans(self::PREFIX . $code, locale: $locale);
    }
}
