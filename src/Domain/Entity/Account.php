<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Enum\AccountType;
use App\Domain\Enum\TransactionType;
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
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'account', cascade: ['persist'])]
    private Collection $transactions;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\OneToMany(targetEntity: Category::class, mappedBy: 'account', cascade: ['persist'])]
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
        if (!$this->canManage($user)) {
            throw new DomainException('User is not allowed to manage this account.');
        }

        if ($this->hasCategoryWithParams($name, $type)) {
            throw new DomainException('Category with this name and type already exists.');
        }

        $category = new Category(Id::generate(), $this, $type, $name, $user);

        $this->categories->add($category);
    }

    public function changeCategoryName(User $user, Id $categoryId, string $name): void
    {
        if (!$this->canManage($user)) {
            throw new DomainException('User is not allowed to manage this account.');
        }

        if (!$category = $this->getCategory($categoryId)) {
            throw new DomainException('Category not found.');
        }

        if (mb_strtolower($name) === $category->name) {
            return;
        }

        if ($this->hasCategoryWithParams($name, $category->type)) {
            throw new DomainException('Category with this name and type already exists.');
        }

        $category->rename($name);
    }

    public function deleteCategory(User $user, Id $categoryId): void
    {
        if (!$this->canManage($user)) {
            throw new DomainException('User is not allowed to manage this account.');
        }

        if (!$category = $this->getCategory($categoryId)) {
            return;
        }

        if (!empty($category->getTransactions())) {
            throw new DomainException('Category has transactions. Delete transactions first.');
        }

        $this->categories->removeElement($category);
    }

    public function addTransaction(User $user, Category $category, Money $money, string $description = ''): void
    {
        if (!$this->canManage($user)) {
            throw new DomainException('User is not allowed to manage this account.');
        }

        if (!$this->hasCategory($category)) {
            throw new DomainException('Account does not have this category.');
        }

        $transaction = new Transaction($this, $user, $category, $money, $description);

        $this->transactions->add($transaction);
        $category->addTransaction($transaction);
    }

    public function addMember(UserAccount $account): void
    {
        $this->members->add($account);
    }

    public function canManage(User $user): bool
    {
        return array_any(
            $this->getMembers(),
            static fn (UserAccount $member) => $user->equals($member->user),
        );
    }

    public function hasCategory(Category $category): bool
    {
        return $this->categories->contains($category);
    }

    public function hasCategoryWithParams(string $name, TransactionType $type): bool
    {
        $name = mb_strtolower($name);

        return $this->categories->exists(
            static fn (int $key, Category $category) => $category->name === $name && $category->type === $type,
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
            static fn (int $key, Category $category) => $category->id->equals($categoryId),
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
}
