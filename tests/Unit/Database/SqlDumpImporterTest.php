<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Unit\Database;

use Doctrine\DBAL\Connection;
use Neusta\Pimcore\TestingFramework\Database\SqlDumpImporter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Filesystem\Filesystem;

final class SqlDumpImporterTest extends TestCase
{
    use ProphecyTrait;

    private Filesystem $filesystem;
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();

        $dir = sys_get_temp_dir() . '/sql-dump-importer-test-' . uniqid();
        $this->filesystem->mkdir($dir);
        $this->tmpDir = (string) realpath($dir);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->tmpDir);
    }

    /** @test */
    #[Test]
    public function it_executes_each_statement_of_a_sql_file_separately(): void
    {
        $this->filesystem->dumpFile($this->tmpDir . '/1.sql', "INSERT INTO a VALUES (1);\nINSERT INTO b VALUES (2);\n");

        $db = $this->prophesize(Connection::class);
        $db->executeStatement('INSERT INTO a VALUES (1);')->willReturn(1)->shouldBeCalledOnce();
        $db->executeStatement('INSERT INTO b VALUES (2);')->willReturn(1)->shouldBeCalledOnce();

        (new SqlDumpImporter())->import($db->reveal(), $this->tmpDir);
    }

    /** @test */
    #[Test]
    public function it_decompresses_gzipped_dump_files(): void
    {
        $this->filesystem->dumpFile($this->tmpDir . '/1.sql.gz', (string) gzencode('INSERT INTO a VALUES (1);'));

        $db = $this->prophesize(Connection::class);
        $db->executeStatement('INSERT INTO a VALUES (1);')->willReturn(1)->shouldBeCalledOnce();

        (new SqlDumpImporter())->import($db->reveal(), $this->tmpDir);
    }

    /** @test */
    #[Test]
    public function it_imports_files_in_natural_sort_order(): void
    {
        $this->filesystem->dumpFile($this->tmpDir . '/10.sql', 'INSERT INTO t VALUES (10);');
        $this->filesystem->dumpFile($this->tmpDir . '/2.sql', 'INSERT INTO t VALUES (2);');

        $executed = [];

        $db = $this->prophesize(Connection::class);
        $db->executeStatement(Argument::type('string'))
            ->will(static function (array $args) use (&$executed) {
                $executed[] = $args[0];

                return 1;
            });

        (new SqlDumpImporter())->import($db->reveal(), $this->tmpDir);

        self::assertSame(['INSERT INTO t VALUES (2);', 'INSERT INTO t VALUES (10);'], $executed);
    }

    /** @test */
    #[Test]
    public function it_resolves_a_relative_dump_location_against_the_project_root(): void
    {
        $relative = 'var/sql-dump-importer-test-relative-' . uniqid();
        $absolute = rtrim(PIMCORE_PROJECT_ROOT, '/') . '/' . $relative;
        $this->filesystem->mkdir($absolute);
        $this->filesystem->dumpFile($absolute . '/1.sql', 'INSERT INTO a VALUES (1);');

        try {
            $db = $this->prophesize(Connection::class);
            $db->executeStatement('INSERT INTO a VALUES (1);')->willReturn(1)->shouldBeCalledOnce();

            (new SqlDumpImporter())->import($db->reveal(), $relative);
        } finally {
            $this->filesystem->remove($absolute);
        }
    }

    /** @test */
    #[Test]
    public function it_throws_for_a_missing_dump_location(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new SqlDumpImporter())->import(
            $this->prophesize(Connection::class)->reveal(),
            $this->tmpDir . '/does-not-exist',
        );
    }
}
