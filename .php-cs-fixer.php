<?php

$fixers = [
    '@PSR2' => true,
    'blank_line_after_opening_tag' => true,
    'braces' => true,
    'concat_space' => ['spacing' => 'none'],
    'no_multiline_whitespace_around_double_arrow' => true,
    'elseif' => true,
    'encoding' => true,
    'single_blank_line_at_eof' => true,
    'no_extra_blank_lines' => true,
    'include' => true,
    'blank_line_after_namespace' => true,
    'not_operator_with_successor_space' => true,
    'constant_case' => ['case' => 'lower'],
    'lowercase_keywords' => true,
    'array_syntax' => ['syntax' => 'short'],
    'no_unused_imports' => true
];

$finder = PhpCsFixer\Finder::create()
    ->name('*.stub')
    ->in([
        __DIR__.'/src',
        __DIR__.'/stubs',
        __DIR__.'/tests',
    ]);

return (new PhpCsFixer\Config())
    ->setFinder($finder)
    ->setRules($fixers)
    ->setUsingCache(false);