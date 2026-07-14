<?php

use Neusta\Pimcore\TestingFramework\Pimcore\PlatformVersion;
use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

$config = (new Configuration())
    // Exclude test app
    ->addPathToExclude(__DIR__ . '/tests/app')

    // Ignore optional dependency
    ->ignoreErrorsOnPackageAndPath('symfony/dotenv', __DIR__ . '/src/Pimcore/BootstrapPimcore.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('dama/doctrine-test-bundle', __DIR__ . '/src/Database/ResetDatabase.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('dama/doctrine-test-bundle', __DIR__ . '/src/Database/DatabaseResetter.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('doctrine/dbal', __DIR__ . '/src/Database/DoctrineSchemaAssetFilter.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('doctrine/orm', __DIR__ . '/src/Database/DoctrineSchemaAssetFilter.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('doctrine/dbal', __DIR__ . '/src/Database/SqlDumpImporter.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('doctrine/dbal', __DIR__ . '/src/Database/PlatformDatabaseInstaller.php', [ErrorType::DEV_DEPENDENCY_IN_PROD]);

if (PlatformVersion::getMajor() < 2026) {
    $config->ignoreErrorsOnPackageAndPath('pimcore/admin-ui-classic-bundle', __DIR__ . '/src/Kernel/TestKernel.php', [ErrorType::DEV_DEPENDENCY_IN_PROD]);

    // Does not exist in 2026.1
    $config->ignoreUnknownClasses(['Pimcore\Bundle\InstallBundle\Database\DatabaseSetup']);
} else {
    // Not installable alongside ^2026.1 at all
    $config->ignoreUnknownClasses(['Pimcore\Bundle\AdminBundle\PimcoreAdminBundle']);
}

return $config;
