<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$header = '@link https://www.yiiframework.com/
@copyright Copyright (c) 2008 Yii Software LLC
@license https://www.yiiframework.com/license/';

$finder = (new Finder())
    ->in([
        __DIR__ . '/commands',
        __DIR__ . '/components',
        __DIR__ . '/helpers',
        __DIR__ . '/models',
        __DIR__ . '/renderers',
        __DIR__ . '/templates',
        __DIR__ . '/tests',
    ])
    ->notPath([
        '#(^|/)data/#',
        '#(^|/)layouts/#',
        '#(^|/)views/#',
    ]);

return (new Config())
    ->setFinder($finder)
    ->setRules([
        'phpdoc_scalar' => true,
        'header_comment' => [
            'comment_type' => 'PHPDoc',
            'header' => $header,
            'location' => 'after_open',
        ],
    ]);
