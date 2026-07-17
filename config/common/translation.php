<?php

declare(strict_types=1);

use App\Application\Gateway\TranslatorInterface;
use App\Infrastructure\Translation\SymfonyTranslator;
use App\Infrastructure\Translation\TranslationFactory;
use Symfony\Component\Translation\Translator;

use function DI\autowire;
use function DI\factory;

return [
    Translator::class => factory(static fn (TranslationFactory $factory): Translator => $factory->create()),
    TranslatorInterface::class => autowire(SymfonyTranslator::class),
];
