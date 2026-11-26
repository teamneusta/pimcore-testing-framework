<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Functional;

use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\Cache as CacheAttribute;
use Neusta\Pimcore\TestingFramework\ConfigurableKernel;
use Neusta\Pimcore\TestingFramework\ConfigurablePimcore;
use Neusta\Pimcore\TestingFramework\ResetDatabase;
use PHPUnit\Framework\Attributes\Test;
use Pimcore\Test\KernelTestCase;

/**
 * Regression test for https://github.com/teamneusta/pimcore-testing-framework/issues/60: after a test with a
 * configuration that requires a booted kernel, the next test could no longer boot a kernel of its own.
 *
 * The configurator used to boot and shut down its kernel through the test case's `bootKernel()` and
 * `ensureKernelShutdown()`. Its reset runs after `tearDown()` (PHPUnit 10+), so the shut-down kernel stayed behind
 * in `KernelTestCase::$kernel`. `ResetDatabase` then created another kernel before the next test, which re-pointed
 * Pimcore's static kernel reference to it. When that test booted a kernel, Symfony first re-booted the stale one
 * to shut it down, and Pimcore resolved its container through the other, dead kernel.
 *
 * `ResetDatabase` is therefore required to reproduce it, even though no test here touches the database.
 * The tests depend on running in the order they are declared.
 */
final class KernelRequiringPimcoreConfigurationTest extends KernelTestCase
{
    use ConfigurableKernel;
    use ConfigurablePimcore;
    use ResetDatabase;

    /** @test */
    #[Test]
    #[CacheAttribute(false)]
    public function a_test_with_a_configuration_that_requires_a_booted_kernel_can_boot_a_kernel(): void
    {
        self::assertTrue(self::bootKernel()->getContainer()->has('kernel'));
    }

    /** @test */
    #[Test]
    public function the_following_test_can_still_boot_a_kernel(): void
    {
        self::assertNull(self::$kernel, 'the configurator must not leave its kernel behind in the test case');
        self::assertTrue(self::bootKernel()->getContainer()->has('kernel'));
    }

    /** @test */
    #[Test]
    public function pimcore_still_points_to_the_kernel_booted_by_the_test(): void
    {
        $kernel = self::bootKernel();

        self::assertSame($kernel, \Pimcore::getKernel());
    }
}
