<?php

use MSpirkov\Yii2\Rector\Rules\AddPropertyTagsRector;
use MSpirkov\Yii2\Rector\Rules\RemoveRedundantPropertyTagsRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/commands',
        __DIR__ . '/components',
        __DIR__ . '/helpers',
        __DIR__ . '/models',
        __DIR__ . '/renderers',
        __DIR__ . '/templates',
        __DIR__ . '/tests',
    ])
    ->withPhpSets(php82: true)
    ->withPHPStanConfigs([
        __DIR__ . '/phpstan.dist.neon',
    ])
    ->withRules([
        AddPropertyTagsRector::class,
        RemoveRedundantPropertyTagsRector::class,
    ])
    ->withSkip([
        __DIR__ . '/tests/data',
    ]);
