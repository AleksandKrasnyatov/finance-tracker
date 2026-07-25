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
    private SeedCatalog $seedCatalog;

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
                new CategoryDto(TransactionType::Income, 'зарплата', 'salary'),
                new CategoryDto(TransactionType::Income, 'другое', 'other'),
                new CategoryDto(TransactionType::Expense, 'продукты', 'groceries'),
                new CategoryDto(TransactionType::Expense, 'кафе', 'cafe'),
                new CategoryDto(TransactionType::Expense, 'транспорт', 'transport'),
                new CategoryDto(TransactionType::Expense, 'жильё', 'housing'),
                new CategoryDto(TransactionType::Expense, 'здоровье', 'health'),
                new CategoryDto(TransactionType::Expense, 'развлечения', 'entertainment'),
                new CategoryDto(TransactionType::Expense, 'другое', 'other'),
            ],
            $catalog->categories(Locale::Ru),
        );
    }
}
