<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\TelegramId;
use App\Infrastructure\Doctrine\Type\IdType;
use App\Infrastructure\Doctrine\Type\TelegramIdType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'users')]
final class User
{
    public function __construct(
        #[ORM\Column(type: IdType::NAME)]
        #[ORM\Id]
        public Id $id,
        #[ORM\Column(type: TelegramIdType::NAME, nullable: true)]
        public ?TelegramId $telegramId,
    ) {
    }

    public static function joinByTelegram(TelegramId $telegramId): self
    {
        return new self(
            Id::generate(),
            $telegramId
        );
    }
}
