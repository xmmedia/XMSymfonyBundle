<?php

declare(strict_types=1);

// Great tool for configuration: https://mlocati.github.io/php-cs-fixer-configurator/

$finder = PhpCsFixer\Finder::create()
    ->in('.')
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony'                  => true,
        '@Symfony:risky'            => true,
        '@PSR2'                     => true,
        '@DoctrineAnnotation'       => true,
        '@PHP71Migration'           => true,
        '@PHP73Migration'           => true,
        '@PHPUnit60Migration:risky' => true,
        'array_syntax'              => [
            'syntax' => 'short',
        ],
        'binary_operator_spaces' => [
            'operators' => [
                '=>' => 'align',
            ],
        ],
        'declare_strict_types'  => true,
        'fopen_flags'           => false,
        'heredoc_indentation'   => false,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'ordered_imports'      => true,
        'protected_to_private' => false,
    ])
    ->setFinder($finder)
;
