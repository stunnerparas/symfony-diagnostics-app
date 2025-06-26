<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);



return (new PhpCsFixer\Config())

    ->setName('Diagnostics Project Coding Standard')

    ->setRules([

        '@PSR12' => true,

        '@Symfony' => true,

        'array_syntax' => ['syntax' => 'short'],

        'ordered_imports' => ['sort_algorithm' => 'alpha'],

        'no_unused_imports' => true,
    ])


    ->setFinder($finder);
