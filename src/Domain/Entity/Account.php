<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Dto\CategoryDto;
use App\Domain\Enum\AccountType;
use App\Domain\Enum\TransactionType;
use App\Domain\Exception\AccountManageException;
use App\Domain\Exception\NoAccessException;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Money;
use App\Infrastructure\Doctrine\Type\IdType;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DomainException;
use Webmozart\Assert\Assert;

#[ORM\Entity]
#[ORM\Table(name: 'accounts')]
final class Account
{
    #[ORM\Column(type: IdType::NAME)]
    #[ORM\Id]
    private(set) Id $id;

    #[ORM\Column(type: Types::STRING, length: 100)]
    private(set) string $name;

    #[ORM\Column(type: Types::ENUM, enumType: AccountType::class)]
    private(set) AccountType $type;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private(set) DateTimeImmutable $createdAt;

    /**
     * @var Collection<int, UserAccount>
     */
    #[ORM\OneToMany(targetEntity: UserAccount::class, mappedBy: 'account')]
    private Collection $members;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(
        targetEntity: Transaction::class,
        mappedBy: 'account',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    private Collection $transactions;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\OneToMany(
        targetEntity: Category::class,
        mappedBy: 'account',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    private Collection $categories;

    public function __construct(
        Id $id,
        string $name,
        AccountType $type,
    ) {
        Assert::notEmpty($name);

        $this->id = $id;
        $this->name = mb_strtolower($name);
        $this->type = $type;
        $this->createdAt = new DateTimeImmutable();
        $this->categories = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->members = new ArrayCollection();
    }

    public static function create(
        User $owner,
        string $name,
        AccountType $type,
    ): self {
        $account = new self(Id::generate(), $name, $type);
        $owner->addAccount($account);

        return $account;
    }

    public function addCategory(User $user, TransactionType $type, string $name): void
    {
        $this->checkAccess($user);

        if ($this->hasCategoryWithParams($name, $type)) {
            throw new AccountManageException('Category with this name and type already exists.');
        }

        $category = new Category(Id::generate(), $this, $type, $name, $user);

        $this->categories->add($category);
    }

    /**
     * @param list<CategoryDto> $categories
     */
    public function addDefaultCategories(User $user, array $categories): void
    {
        $this->checkAccess($user);

        if (!$this->categories->isEmpty()) {
            throw new AccountManageException('Account already has categories.');
        }

        foreach ($categories as $category) {
            $this->categories->add(new Category(
                Id::generate(),
                $this,
                $category->type,
                $category->name,
                $user,
            ));
        }
    }

    public function changeCategoryName(User $user, Id $categoryId, string $name): void
    {
        $this->checkAccess($user);

        if (!$category = $this->getCategory($categoryId)) {
            throw new AccountManageException('Category not found.');
        }

        if (mb_strtolower($name) === $category->name) {
            return;
        }

        if ($this->hasCategoryWithParams($name, $category->type)) {
            throw new AccountManageException('Category with this name and type already exists.');
        }

        $category->rename($name);
    }

    public function deleteCategory(User $user, Id $categoryId): void
    {
        $this->checkAccess($user);

        if (!$category = $this->getCategory($categoryId)) {
            return;
        }

        if (!empty($category->getTransactions())) {
            throw new AccountManageException('Category has transactions. Delete transactions first.');
        }

        $this->categories->removeElement($category);
    }

    public function addTransaction(
        User $user,
        Category $category,
        Money $money,
        string $description = '',
        ?DateTimeImmutable $date = null,
    ): void {
        $this->checkAccess($user);

        if (!$this->hasCategory($category)) {
            throw new AccountManageException('Account does not have this category.');
        }

        $transaction = new Transaction($this, $user, $category, $money, $description, $date);

        $this->transactions->add($transaction);
        $category->addTransaction($transaction);
    }

    public function changeTransactionCategory(User $user, Id $transactionId, Category $category): void
    {
        $this->checkAccess($user);

        if (!$this->hasCategory($category)) {
            throw new AccountManageException('Account does not have this category.');
        }

        if (!$transaction = $this->getTransaction($transactionId)) {
            throw new AccountManageException('Transaction not found.');
        }

        $transaction->category->removeTransaction($transaction);
        $transaction->changeCategory($user, $category);
        $category->addTransaction($transaction);
    }

    public function changeTransactionMoney(User $user, Id $transactionId, Money $money): void
    {
        $this->checkAccess($user);

        if (!$transaction = $this->getTransaction($transactionId)) {
            throw new AccountManageException('Transaction not found.');
        }

        $transaction->changeMoney($user, $money);
    }

    public function changeTransactionDate(User $user, Id $transactionId, DateTimeImmutable $date): void
    {
        $this->checkAccess($user);

        if (!$transaction = $this->getTransaction($transactionId)) {
            throw new AccountManageException('Transaction not found.');
        }

        $transaction->changeDate($user, $date);
    }

    public function changeTransactionDescription(User $user, Id $transactionId, string $description): void
    {
        $this->checkAccess($user);

        if (!$transaction = $this->getTransaction($transactionId)) {
            throw new AccountManageException('Transaction not found.');
        }

        $transaction->changeDescription($user, $description);
    }

    public function deleteTransaction(User $user, Id $transactionId): void
    {
        $this->checkAccess($user);

        if (!$transaction = $this->getTransaction($transactionId)) {
            return;
        }

        $transaction->category->removeTransaction($transaction);
        $this->transactions->removeElement($transaction);
    }

    public function addMember(UserAccount $account): void
    {
        $this->members->add($account);
    }

    public function canManage(User $user): bool
    {
        return array_any(
            $this->getMembers(),
            static fn(UserAccount $member) => $user->equals($member->user),
        );
    }

    public function canView(User $user): bool
    {
        return $this->canManage($user);
    }

    public function hasCategory(Category $category): bool
    {
        return $this->categories->contains($category);
    }

    public function hasCategoryWithParams(string $name, TransactionType $type): bool
    {
        $name = mb_strtolower($name);

        return $this->categories->exists(
            static fn(int $key, Category $category) => $category->name === $name && $category->type === $type,
        );
    }

    public function getTransaction(Id $transactionId): ?Transaction
    {
        return $this->transactions->findFirst(
            static fn(int $key, Transaction $transaction) => $transaction->id->equals($transactionId),
        );
    }

    /**
     * @return Transaction[]
     */
    public function getTransactions(): array
    {
        return $this->transactions->toArray();
    }

    public function getCategory(Id $categoryId): ?Category
    {
        return $this->categories->findFirst(
            static fn(int $key, Category $category) => $category->id->equals($categoryId),
        );
    }

    /**
     * @return Category[]
     */
    public function getCategories(): array
    {
        return $this->categories->toArray();
    }

    /**
     * @return UserAccount[]
     */
    public function getMembers(): array
    {
        return $this->members->toArray();
    }

    private function checkAccess(User $user): void
    {
        if (!$this->canManage($user)) {
            throw new NoAccessException('User is not allowed to manage this account.');
        }
    }
}
