<?php

use Neusta\Pimcore\TestingFramework\Pimcore\PlatformVersion;
use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

$config = (new Configuration())
    // Exclude test app
    ->addPathToExclude(__DIR__ . '/tests/app')

    // Ignore packages that the analyzer does not recognize
    ->ignoreErrorsOnPackage('symfony/deprecation-contracts', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackage('symfony/contracts', [ErrorType::SHADOW_DEPENDENCY])

    // Ignore optional dependency
    ->ignoreErrorsOnPackageAndPath('symfony/dotenv', __DIR__ . '/src/BootstrapPimcore.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('dama/doctrine-test-bundle', __DIR__ . '/src/ResetDatabase.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('dama/doctrine-test-bundle', __DIR__ . '/src/Internal/Database/DatabaseResetter.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('doctrine/dbal', __DIR__ . '/src/Internal/Database/DoctrineSchemaAssetFilter.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('doctrine/orm', __DIR__ . '/src/Internal/Database/DoctrineSchemaAssetFilter.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('doctrine/dbal', __DIR__ . '/src/Internal/Database/SqlDumpImporter.php', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackageAndPath('doctrine/dbal', __DIR__ . '/src/Internal/Database/PimcoreDatabaseInstaller.php', [ErrorType::DEV_DEPENDENCY_IN_PROD]);

if (PlatformVersion::getMajor() < 2026) {
    $config->ignoreErrorsOnPackageAndPath('pimcore/admin-ui-classic-bundle', __DIR__ . '/src/TestKernel.php', [ErrorType::DEV_DEPENDENCY_IN_PROD]);

    // Does not exist in 2026.1
    $config->ignoreUnknownClasses(['Pimcore\Bundle\InstallBundle\Database\DatabaseSetup']);
} else {
    // Not installable alongside ^2026.1 at all
    $config->ignoreUnknownClasses(['Pimcore\Bundle\AdminBundle\PimcoreAdminBundle']);
}

return $config;
