<?php

declare(strict_types=1);

namespace Test\Unit\Application\Service;

use App\Application\Service\SeedCatalog;
use App\Domain\Dto\CategoryDto;
use App\Domain\Entity\Category;
use App\Domain\Enum\Locale;
use App\Domain\Enum\TransactionType;
use App\Infrastructure\Translation\SymfonyTranslator;
use App\Infrastructure\Translation\TranslationFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SeedCatalogTest extends TestCase
{
    private seedCatalog $seedCatalog;

    protected function setUp(): void
    {
        $this->seedCatalog = new SeedCatalog(new SymfonyTranslator(new TranslationFactory()->create()));

        parent::setUp();
    }

    #[Test]
    public function resolvesEnglishSeeds(): void
    {
        $catalog = $this->seedCatalog;

        self::assertSame('main', $catalog->accountName(Locale::En));
        self::assertEquals(
            Category::defaults(),
            $catalog->categories(Locale::default()),
        );
    }

    #[Test]
    public function resolvesRussianSeeds(): void
    {
        $catalog = $this->seedCatalog;

        self::assertSame('основной', $catalog->accountName(Locale::Ru));
        self::assertEquals(
            [
                new CategoryDto(TransactionType::Income, 'зарплата'),
                new CategoryDto(TransactionType::Income, 'другое'),
                new CategoryDto(TransactionType::Expense, 'продукты'),
                new CategoryDto(TransactionType::Expense, 'кафе'),
                new CategoryDto(TransactionType::Expense, 'транспорт'),
                new CategoryDto(TransactionType::Expense, 'жильё'),
                new CategoryDto(TransactionType::Expense, 'здоровье'),
                new CategoryDto(TransactionType::Expense, 'развлечения'),
                new CategoryDto(TransactionType::Expense, 'другое'),
            ],
            $catalog->categories(Locale::Ru),
        );
    }
}
