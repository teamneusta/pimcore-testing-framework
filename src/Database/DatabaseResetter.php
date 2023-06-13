<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Database;

use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @internal
 */
final class DatabaseResetter
{
    private static bool $hasBeenReset = false;

    public static function hasBeenReset(): bool
    {
        if (isset($_SERVER['DISABLE_DATABASE_RESET'])) {
            return true;
        }

        return self::$hasBeenReset;
    }

    public static function isDAMADoctrineTestBundleEnabled(): bool
    {
        return class_exists(StaticDriver::class) && StaticDriver::isKeepStaticConnections();
    }

    public static function resetDatabase(KernelInterface $kernel): void
    {
        if (!$databaseResetter = self::createResetter($kernel)) {
            return;
        }

        $databaseResetter->resetDatabase();

        self::$hasBeenReset = true;
    }

    public static function resetSchema(KernelInterface $kernel): void
    {
        if (self::isDAMADoctrineTestBundleEnabled()) {
            // not required as the DAMADoctrineTestBundle wraps each test in a transaction
            return;
        }

        if (!$databaseResetter = self::createResetter($kernel)) {
            return;
        }

        $databaseResetter->resetSchema();
    }

    private static function createResetter(KernelInterface $kernel): ?PimcoreDatabaseResetter
    {
        if (!$registry = $kernel->getContainer()->get('doctrine')) {
            return null;
        }

        return new PimcoreDatabaseResetter(self::createApplication($kernel), $registry);
    }

    private static function createApplication(KernelInterface $kernel): Application
    {
        $application = new \Pimcore\Console\Application($kernel);
        $application->setAutoExit(false);

        return $application;
    }
}
