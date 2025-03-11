<?php

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    // Exclude test app
    ->addPathToExclude(__DIR__ . '/tests/app')

    // Ignore false-positive unused dependency
    ->ignoreErrorsOnPackage('symfony/browser-kit', [ErrorType::UNUSED_DEPENDENCY])

    // Ignore optional dependency
    ->ignoreErrorsOnPackageAndPath('dama/doctrine-test-bundle', __DIR__ . '/src/Database/ResetDatabase.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('dama/doctrine-test-bundle', __DIR__ . '/src/Database/DatabaseResetter.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])

    // Ignore a legacy class from Pimcore 10
    ->ignoreUnknownClasses([Pimcore\Bundle\AdminBundle\PimcoreAdminBundle::class])
;
