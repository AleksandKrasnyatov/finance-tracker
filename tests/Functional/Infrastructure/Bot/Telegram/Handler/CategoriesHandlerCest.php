<?php

declare(strict_types=1);

namespace Test\Functional\Infrastructure\Bot\Telegram\Handler;

use App\Application\Gateway\TranslatorInterface;
use App\Domain\Entity\Category;
use App\Domain\Entity\Transaction;
use App\Domain\Enum\TransactionType;
use App\Domain\ValueObject\Id;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Testing\FakeNutgram;
use Test\Support\Fixture\OnboardedTelegramUserFixture;
use Test\Support\FunctionalTester;
use Test\Support\TelegramBotTester;

final class CategoriesHandlerCest
{
    private FakeNutgram $bot;

    public function _before(FunctionalTester $I): void
    {
        $I->loadFixtures(OnboardedTelegramUserFixture::class);
        $this->bot = TelegramBotTester::configure($I, OnboardedTelegramUserFixture::TELEGRAM_ID);
    }

    public function givenUserHasAccountWhenOpensCategoriesThenTypePickerIsShown(): void
    {
        $this->bot
            ->hearText('/category')
            ->reply()
            ->assertReplyText('Categories');
    }

    public function givenUserOpensExpenseTypeWhenHasDefaultCategoriesThenTypeListIsShown(): void
    {
        $this->bot
            ->hearText('/category')
            ->reply();

        $this->bot
            ->hearCallbackQueryData('cat:type:expense')
            ->reply()
            ->assertReply('editMessageText', [
                'text' => 'Categories: expense',
            ], 1);
    }

    public function givenUserOpensCategoryWhenCategoryExistsThenDetailIsShown(FunctionalTester $I): void
    {
        $id = $this->expenseCategoryId($I, 'groceries');

        $this->bot
            ->hearText('/category')
            ->reply();

        $this->bot
            ->hearCallbackQueryData('cat:type:expense')
            ->reply();

        $this->bot
            ->hearCallbackQueryData('cat:view:' . $id)
            ->reply()
            ->assertReply('editMessageText', [
                'text' => 'groceries (expense)',
            ], 1);
    }

    public function givenUserViewsMissingCategoryWhenOpensDetailThenNotFoundErrorIsShown(
        FunctionalTester $I,
    ): void {
        /** @var TranslatorInterface $translator */
        $translator = $I->grabService(TranslatorInterface::class);

        $this->bot
            ->hearCallbackQueryData('cat:view:' . Id::generate()->value)
            ->reply()
            ->assertReply('answerCallbackQuery', [
                'text' => $translator->trans('error.notFound.Category'),
                'show_alert' => true,
            ]);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function givenUserConfirmsDeleteWhenCategoryHasNoTransactionsThenCategoryIsRemoved(
        FunctionalTester $I,
    ): void {
        $id = $this->expenseCategoryId($I, 'cafe');

        $this->bot
            ->hearText('/category')
            ->reply();

        $this->bot
            ->hearCallbackQueryData('cat:type:expense')
            ->reply();

        $this->bot
            ->hearCallbackQueryData('cat:view:' . $id)
            ->reply();

        $this->bot
            ->hearCallbackQueryData('cat:delete:' . $id . ':expense')
            ->reply()
            ->assertReply('editMessageText', [
                'text' => 'Delete category "cafe"?',
            ], 1);

        $this->bot
            ->hearCallbackQueryData('cat:delete_ok:' . $id . ':expense')
            ->reply()
            ->assertReply('editMessageText', [
                'text' => 'Categories: expense',
            ], 1);

        $I->dontSeeInRepository(Category::class, [
            'name' => 'cafe',
            'type' => TransactionType::Expense,
        ]);
    }

    public function givenUserDeletesCategoryWhenItHasTransactionsThenErrorIsShown(FunctionalTester $I): void
    {
        $this->bot
            ->hearText('-100 groceries')
            ->reply();

        $id = $this->expenseCategoryId($I, 'groceries');

        /** @var TranslatorInterface $translator */
        $translator = $I->grabService(TranslatorInterface::class);

        $this->bot
            ->hearCallbackQueryData('cat:delete_ok:' . $id . ':expense')
            ->reply()
            ->assertReply('answerCallbackQuery', [
                'text' => $translator->trans('error.accountManage'),
                'show_alert' => true,
            ]);

        $I->seeInRepository(Category::class, [
            'name' => 'groceries',
            'type' => TransactionType::Expense,
        ]);
        $I->seeInRepository(Transaction::class, [
            'category' => [
                'name' => 'groceries',
                'type' => TransactionType::Expense,
            ],
        ]);
    }

    private function expenseCategoryId(FunctionalTester $I, string $name): string
    {
        return $I->grabEntityFromRepository(Category::class, [
            'name' => $name,
            'type' => TransactionType::Expense,
        ])->id->value;
    }
}
