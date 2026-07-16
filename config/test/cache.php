<?php

declare(strict_types=1);

use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

return [
    CacheInterface::class => static fn (): CacheInterface => new Psr16Cache(new ArrayAdapter()),
];
