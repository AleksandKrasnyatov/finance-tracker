<?php

declare(strict_types=1);

namespace Test\Unit\Domain\ValueObject;

use App\Domain\Enum\Currency;
use App\Domain\ValueObject\Money;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    #[Test]
    public function givenValidParamsWhenMoneyIsCreatedThenParamsMatches(): void
    {
        $money = new Money($amount = '123.12', $currency = Currency::USD);

        self::assertEquals($amount, $money->amount);
        self::assertEquals($currency, $money->currency);
    }

    /**
     * @dataProvider invalidAmounts
     */
    #[Test]
    public function givenInvalidAmountWhenMoneyIsCreatedThenInvalidArgumentExceptionIsExpected(string $invalidAmount): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Money($invalidAmount);
    }

    /**
     * @return list<string[]>
     */
    public static function invalidAmounts(): array
    {
        return [
            [''],
            ['string'],
            ['-1'],
            ['-100.123'],
        ];
    }
}
