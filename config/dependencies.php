<?php

declare(strict_types=1);

use Slim\Views\Twig;

return [
    Twig::class => static fn (): Twig => Twig::create('/app/src/Infrastructure/Http/Template', ['cache' => false]),
];
