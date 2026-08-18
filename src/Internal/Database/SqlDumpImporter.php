<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Internal\Database;

use Doctrine\DBAL\Connection;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Imports a directory of `.sql`/`.sql.gz` dump files directly via the DBAL connection.
 *
 * @internal
 */
final class SqlDumpImporter
{
    private const SQL_FILE_EXTENSION = '.sql';
    private const SQL_GZIP_FILE_EXTENSION = '.sql.gz';

    public function import(Connection $db, string $dumpLocation): void
    {
        foreach ($this->getDataFiles($this->resolveDumpLocation($dumpLocation)) as $file) {
            $sql = str_ends_with($file, self::SQL_GZIP_FILE_EXTENSION)
                ? (string) gzdecode((string) file_get_contents($file))
                : (string) file_get_contents($file);

            // Mirrors the naive semicolon-splitting Pimcore's own install.sql execution already relies on.
            foreach (explode(';', $sql) as $statement) {
                if ('' !== $statement = trim($statement)) {
                    $db->executeStatement($statement . ';');
                }
            }
        }
    }

    public function resolveDumpLocation(string $dumpLocation): string
    {
        $filesystem = new Filesystem();
        $dumpLocation = $filesystem->isAbsolutePath($dumpLocation)
            ? rtrim($dumpLocation, '/')
            : PIMCORE_PROJECT_ROOT . '/' . trim($dumpLocation, '/');

        if (!$filesystem->exists($dumpLocation)) {
            throw new \InvalidArgumentException(\sprintf('The directory "%s" does not exist.', $dumpLocation));
        }

        return (string) realpath($dumpLocation);
    }

    /**
     * @return array<string>
     */
    public function getDataFiles(string $dumpLocation): array
    {
        $files = [
            ...glob($dumpLocation . '/*' . self::SQL_FILE_EXTENSION, \GLOB_NOSORT) ?: [],
            ...glob($dumpLocation . '/*' . self::SQL_GZIP_FILE_EXTENSION, \GLOB_NOSORT) ?: [],
        ];

        natsort($files);

        return $files;
    }
}
