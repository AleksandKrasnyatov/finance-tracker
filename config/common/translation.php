<?php

declare(strict_types=1);

use App\Infrastructure\Translation\TranslationFactory;
use Symfony\Component\Translation\Translator;

use function DI\factory;

return [
    Translator::class => factory(static fn (TranslationFactory $factory): Translator => $factory->create()),
];
