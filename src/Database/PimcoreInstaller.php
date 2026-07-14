<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Database;

use Pimcore\Bundle\InstallBundle\Installer;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Only for Pimcore 11/12.
 *
 * @internal
 */
class PimcoreInstaller extends Installer
{
    private ?string $dumpLocation = null;
    private SqlDumpImporter $sqlDumpImporter;

    public function __construct()
    {
        parent::__construct(new NullLogger(), new EventDispatcher());
        $this->sqlDumpImporter = new SqlDumpImporter();

        $this->setImportDatabaseDataDump(false);
    }

    public function setDumpLocation(string $dumpLocation): void
    {
        $this->dumpLocation = $this->sqlDumpImporter->resolveDumpLocation($dumpLocation);

        $this->setImportDatabaseDataDump(true);
    }

    /**
     * @return array<string>
     */
    protected function getDataFiles(): array
    {
        if ($this->dumpLocation) {
            return $this->sqlDumpImporter->getDataFiles($this->dumpLocation);
        }

        return [];
    }
}
