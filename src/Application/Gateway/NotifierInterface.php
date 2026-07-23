<?php

declare(strict_types=1);

namespace App\Application\Gateway;

use App\Domain\ValueObject\Id;

interface NotifierInterface
{
    public function notify(Id $userId, Notification $notification): void;
}
