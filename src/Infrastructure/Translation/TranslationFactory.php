<?php

declare(strict_types=1);

namespace App\Infrastructure\Translation;

use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;

final class TranslationFactory
{
    public function create(): Translator
    {
        $translator = new Translator('en');
        $translator->setFallbackLocales(['en']);
        $translator->addLoader('yaml', new YamlFileLoader());

        $translationsDir = __DIR__ . '/translations';
        $translator->addResource('yaml', $translationsDir . '/messages.ru.yaml', 'ru');
        $translator->addResource('yaml', $translationsDir . '/messages.en.yaml', 'en');

        return $translator;
    }
}
