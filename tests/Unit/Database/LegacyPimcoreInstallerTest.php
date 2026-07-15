<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Unit\Database;

use Neusta\Pimcore\TestingFramework\Database\LegacyPimcoreInstaller;
use Neusta\Pimcore\TestingFramework\Pimcore\PlatformVersion;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

final class LegacyPimcoreInstallerTest extends TestCase
{
    private Filesystem $filesystem;
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();

        $dir = sys_get_temp_dir() . '/pimcore-installer-test-' . uniqid();
        $this->filesystem->mkdir($dir);
        $this->tmpDir = realpath($dir);

        // `LegacyPimcoreInstaller` extends Pimcore's `Installer`, which is `final` on ^2026.1 - merely
        // loading the class (e.g. via `new LegacyPimcoreInstaller()` below) would be a fatal error there.
        if (PlatformVersion::getMajor() >= 2026) {
            self::markTestSkipped('LegacyPimcoreInstaller only supports Pimcore 11/12.');
        }
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->tmpDir);
    }

    /** @test */
    #[Test]
    public function it_has_no_data_files_by_default(): void
    {
        self::assertSame([], $this->getDataFiles(new LegacyPimcoreInstaller()));
    }

    /** @test */
    #[Test]
    public function it_resolves_an_absolute_dump_location_with_trailing_slash(): void
    {
        $installer = new LegacyPimcoreInstaller();
        $installer->setDumpLocation($this->tmpDir . '/');

        $this->filesystem->touch($this->tmpDir . '/dump.sql');

        self::assertSame([$this->tmpDir . '/dump.sql'], $this->getDataFiles($installer));
    }

    /** @test */
    #[Test]
    public function it_resolves_a_dump_location_relative_to_the_project_root(): void
    {
        $relative = 'var/pimcore-installer-test-relative-' . uniqid();
        $absolute = rtrim(PIMCORE_PROJECT_ROOT, '/') . '/' . $relative;
        $this->filesystem->mkdir($absolute);

        try {
            $installer = new LegacyPimcoreInstaller();
            $installer->setDumpLocation($relative);

            $this->filesystem->touch($absolute . '/dump.sql');

            self::assertSame([$absolute . '/dump.sql'], $this->getDataFiles($installer));
        } finally {
            $this->filesystem->remove($absolute);
        }
    }

    /** @test */
    #[Test]
    public function it_throws_for_a_missing_dump_location(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new LegacyPimcoreInstaller())->setDumpLocation($this->tmpDir . '/does-not-exist');
    }

    /** @test */
    #[Test]
    public function it_naturally_sorts_sql_and_gzipped_dump_files(): void
    {
        foreach (['10.sql', '2.sql', '1.sql', '3.sql.gz'] as $file) {
            $this->filesystem->touch($this->tmpDir . '/' . $file);
        }
        $this->filesystem->touch($this->tmpDir . '/not-a-dump.txt');

        $installer = new LegacyPimcoreInstaller();
        $installer->setDumpLocation($this->tmpDir);

        self::assertSame(
            [
                $this->tmpDir . '/1.sql',
                $this->tmpDir . '/2.sql',
                $this->tmpDir . '/3.sql.gz',
                $this->tmpDir . '/10.sql',
            ],
            array_values($this->getDataFiles($installer)),
        );
    }

    /**
     * @return array<string>
     */
    private function getDataFiles(LegacyPimcoreInstaller $installer): array
    {
        return (new \ReflectionMethod($installer, 'getDataFiles'))->invoke($installer);
    }
}
