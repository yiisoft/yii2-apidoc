<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = (new Finder())
    ->in([
        __DIR__ . '/commands',
        __DIR__ . '/components',
        __DIR__ . '/helpers',
        __DIR__ . '/models',
        __DIR__ . '/renderers',
        __DIR__ . '/templates',
        __DIR__ . '/tests',
    ])->notPath([
        // PHP CS Fixer doesn't work well with view, so we exclude it
        '#views#',
        '#layouts#',
    ]);

return (new Config())
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRules([
        '@PER-CS3.0' => true,
        'no_unused_imports' => true,
        'ordered_class_elements' => true,
        'class_attributes_separation' => ['elements' => ['method' => 'one']],
    ])
    ->setFinder($finder);
