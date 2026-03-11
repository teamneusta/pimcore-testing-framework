<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Database;

use Pimcore\Bundle\InstallBundle\Installer;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
class PimcoreInstaller extends Installer
{
    private const SQL_FILE_EXTENSION = '.sql';
    private const SQL_GZIP_FILE_EXTENSION = '.sql.gz';

    private ?string $dumpLocation = null;

    public function __construct()
    {
        parent::__construct(new NullLogger(), new EventDispatcher());
        $this->setImportDatabaseDataDump(false);
    }

    public function setDumpLocation(string $dumpLocation): void
    {
        $filesystem = new Filesystem();
        $dumpLocation = $filesystem->isAbsolutePath($dumpLocation)
            ? rtrim($dumpLocation, '/')
            : PIMCORE_PROJECT_ROOT . '/' . trim($dumpLocation, '/');

        if (!$filesystem->exists($dumpLocation)) {
            throw new \InvalidArgumentException(\sprintf('The directory "%s" does not exist.', $dumpLocation));
        }

        $this->dumpLocation = realpath($dumpLocation);
        $this->setImportDatabaseDataDump(true);
    }

    /**
     * @return array<string>
     */
    protected function getDataFiles(): array
    {
        if (!$this->dumpLocation) {
            return [];
        }

        $files = [
            ...glob($this->dumpLocation . '/*' . self::SQL_FILE_EXTENSION, \GLOB_NOSORT) ?: [],
            ...glob($this->dumpLocation . '/*' . self::SQL_GZIP_FILE_EXTENSION, \GLOB_NOSORT) ?: [],
        ];

        natsort($files);

        return $files;
    }
}
