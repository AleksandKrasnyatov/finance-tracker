<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Enum\Currency;
use Doctrine\DBAL\Types\Types;
use Webmozart\Assert\Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class Money
{
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    public string $amount;
    #[ORM\Column(type: Types::ENUM, enumType: Currency::class)]
    public Currency $currency;

    public function __construct(string $amount, Currency $currency = Currency::RUB)
    {
        Assert::numeric($amount);
        Assert::greaterThanEq($amount, 0);
        $this->amount = $amount;
        $this->currency = $currency;
    }
}
