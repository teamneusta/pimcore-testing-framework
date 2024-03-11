<?php
declare(strict_types=1);

namespace Functional;

use Neusta\Pimcore\TestingFramework\Kernel\TestKernel;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableKernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

class KernelShutdownTest extends ConfigurableKernelTestCase
{
    /**
     * @test
     */
    public function it_does_not_cleanup_the_cache_directory_of_the_standard_kernel(): void
    {
        $cacheDirectory = self::bootKernel()->getCacheDir();
        $filesystem = new Filesystem();

        self::assertTrue($filesystem->exists($cacheDirectory));

        self::ensureKernelShutdown();

        self::assertTrue($filesystem->exists($cacheDirectory));
    }

    /**
     * @test
     */
    public function it_does_cleanup_the_cache_directory_of_the_dynamic_kernel(): void
    {
        $kernel = self::bootKernel(['config' => function (TestKernel $kernel) {
            $kernel->addTestExtensionConfig('framework', ['secret' => 'foo']);
        }]);

        $cacheDirectory = $kernel->getCacheDir();
        $filesystem = new Filesystem();

        self::assertTrue($filesystem->exists($cacheDirectory));

        self::ensureKernelShutdown();

        self::assertFalse($filesystem->exists($cacheDirectory));
    }
}
