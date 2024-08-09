<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Pimcore\Bundle\InstallBundle\Installer;
use Pimcore\Db;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
class PimcoreInstaller extends Installer
{
    private const SQL_FILE_EXTENSION = '.sql';
    private const GZIP_FILE_EXTENSION = '.sql.gz';
    private const DUMP_FILE_EXTENSIONS = [
        self::SQL_FILE_EXTENSION,
        self::GZIP_FILE_EXTENSION,
    ];

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
     * @param Connection $db
     * @param string     $file
     *
     * @throws Exception
     */
    public function insertDatabaseDump($db, $file = ''): void
    {
        // Todo: remove when support for Pimcore <11.2.2 is dropped
        if (1 === \func_num_args()) {
            $file = func_get_arg(0);
            $db = Db::get();
        }

        if (str_ends_with($file, self::GZIP_FILE_EXTENSION)) {
            $file = 'compress.zlib://' . $file;
        }

        $dumpFile = file_get_contents($file);

        if (str_contains($file, 'atomic')) {
            $db->executeStatement($dumpFile);
        } else {
            // get every command as single part - ; at end of line
            $singleQueries = explode(";\n", $dumpFile);

            // execute queries in bulk mode to prevent max_packet_size errors
            $batchQueries = [];
            foreach ($singleQueries as $m) {
                $sql = trim($m);
                if ('' !== $sql) {
                    $batchQueries[] = $sql . ';';
                }

                if (\count($batchQueries) > 500) {
                    $db->executeStatement(implode("\n", $batchQueries));
                    $batchQueries = [];
                }
            }

            $db->executeStatement(implode("\n", $batchQueries));
        }

        // set the id of the system user to 0
        $db->update('users', ['id' => 0], ['name' => 'system']);
    }

    protected function getDataFiles(): array
    {
        if (!$this->dumpLocation) {
            return [];
        }

        $pattern = \sprintf('%s/*{%s}', $this->dumpLocation, implode(',', self::DUMP_FILE_EXTENSIONS));

        return glob($pattern, \GLOB_BRACE);
    }
}
