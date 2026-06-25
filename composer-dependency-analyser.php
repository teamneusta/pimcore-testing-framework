<?php

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    // Exclude test app
    ->addPathToExclude(__DIR__ . '/tests/app')

    // Ignore optional dependency
    ->ignoreErrorsOnPackageAndPath('symfony/dotenv', __DIR__ . '/src/Pimcore/BootstrapPimcore.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('dama/doctrine-test-bundle', __DIR__ . '/src/Database/ResetDatabase.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('dama/doctrine-test-bundle', __DIR__ . '/src/Database/DatabaseResetter.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('doctrine/dbal', __DIR__ . '/src/Database/DoctrineSchemaAssetFilter.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('doctrine/orm', __DIR__ . '/src/Database/DoctrineSchemaAssetFilter.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
;
