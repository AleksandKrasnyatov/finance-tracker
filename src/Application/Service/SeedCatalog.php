<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Gateway\TranslatorInterface;
use App\Domain\Dto\CategoryDto;
use App\Domain\Entity\Category;
use App\Domain\Enum\Locale;

final readonly class SeedCatalog
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function accountName(Locale $locale): string
    {
        return $this->translator->trans('main', locale: $locale);
    }

    /**
     * @return list<CategoryDto>
     */
    public function categories(Locale $locale): array
    {
        if ($locale === Locale::default()) {
            return Category::defaults();
        }

        $categories = [];

        foreach (Category::defaults() as $category) {
            $categories[] = new CategoryDto(
                $category->type,
                $this->translator->trans($category->name, locale: $locale),
            );
        }

        return $categories;
    }
}
