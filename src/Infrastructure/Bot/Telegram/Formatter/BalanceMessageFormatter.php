<?php

declare(strict_types=1);

namespace App\Infrastructure\Bot\Telegram\Formatter;

use App\Application\Gateway\TranslatorInterface;
use App\Application\UseCase\Account\Query\GetAccountBalanceResult;
use App\Domain\Enum\Currency;
use App\Domain\Enum\Locale;

final readonly class BalanceMessageFormatter
{
    private const int BAR_LENGTH = 10;

    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function format(GetAccountBalanceResult $result, Locale $locale): string
    {
        $percent = $this->remainingIncomePercent($result->balance, $result->incomes);

        return $this->translator->trans('bot.balance.message', [
            '%month%' => $this->translator->trans('bot.month.' . $result->month, locale: $locale),
            '%balance%' => $this->formatSignedMoney($result->balance, $result->currency),
            '%incomes%' => $this->formatMoney($result->incomes, $result->currency),
            '%expenses%' => $this->formatMoney($result->expenses, $result->currency),
            '%bar%' => $this->progressBar($percent),
            '%percent%' => (string)$percent,
        ], $locale);
    }

    private function remainingIncomePercent(int $balance, int $incomes): int
    {
        if ($incomes <= 0) {
            return $balance >= 0 ? 100 : 0;
        }

        return max(0, min(100, (int)round($balance / $incomes * 100)));
    }

    private function progressBar(int $percent): string
    {
        $filled = max(0, min(self::BAR_LENGTH, (int)round($percent / 100 * self::BAR_LENGTH)));

        return str_repeat('💚', $filled) . str_repeat('🩸', self::BAR_LENGTH - $filled);
    }

    private function formatSignedMoney(int $amount, string $currency): string
    {
        $sign = $amount >= 0 ? '+' : '−';

        return $sign . $this->formatMoney(abs($amount), $currency);
    }

    private function formatMoney(int $amount, string $currency): string
    {
        return number_format($amount, thousands_separator:  ' ') . ' ' . $this->currencySymbol($currency);
    }

    private function currencySymbol(string $currency): string
    {
        return Currency::tryFrom($currency)?->symbol() ?? $currency;
    }
}
