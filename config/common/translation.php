<?php

declare(strict_types=1);

use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

return [
    TranslatorInterface::class => static function (): TranslatorInterface {
        $translator = new Translator('ru');
        $translator->setFallbackLocales(['ru']);
        $translator->addLoader('yaml', new YamlFileLoader());

        $translationsDir = dirname(__DIR__, 2) . '/translations';
        $translator->addResource('yaml', $translationsDir . '/messages.ru.yaml', 'ru');
        $translator->addResource('yaml', $translationsDir . '/messages.en.yaml', 'en');

        return $translator;
    },
];
