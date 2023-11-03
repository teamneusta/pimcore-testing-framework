<?php

/*
 * This document has been initially generated with
 * https://mlocati.github.io/php-cs-fixer-configurator/#version:3.5.0|configurator
 * and then adapted to our needs
 */

return (new PhpCsFixer\Config)
    ->setFinder((new PhpCsFixer\Finder)
        ->in([
            __DIR__ . '/src',
            __DIR__ . '/tests',
        ])
        ->notPath(['DependencyInjection/Configuration.php', 'app/var'])
    )
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,

        // declare strict types must be on first line after opening tag
        'blank_line_after_opening_tag' => false, // overwrite @Symfony
        'linebreak_after_opening_tag' => false, // overwrite @Symfony
        'declare_strict_types' => true, // custom

        // allow throw's in multiple lines, so message can be a long string
        'single_line_throw' => false, // overwrite @Symfony

        // we want spaces
        'concat_space' => ['spacing' => 'one'], // overwrite @Symfony

        // we want to leave the choice to the developer,
        // because some people have their own style of naming test methods
        'php_unit_method_casing' => false, // overwrite @Symfony

        // we want to leave the choice to the developer
        'php_unit_test_annotation' => false, // overwrite @Symfony:risky
    ]);
