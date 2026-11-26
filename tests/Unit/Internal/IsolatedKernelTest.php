<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Unit\Internal;

use Neusta\Pimcore\TestingFramework\Internal\IsolatedKernel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Service\ResetInterface;

final class IsolatedKernelTest extends TestCase
{
    private ?KernelInterface $pimcoreKernel;

    protected function setUp(): void
    {
        $this->pimcoreKernel = \Pimcore::getKernel();
    }

    protected function tearDown(): void
    {
        // `Pimcore::setKernel()` does not accept null, and a test may have replaced it with a mock.
        (new \ReflectionProperty(\Pimcore::class, 'kernel'))->setValue(null, $this->pimcoreKernel);
    }

    /** @test */
    #[Test]
    public function it_boots_the_created_kernel(): void
    {
        $kernel = $this->createMock(KernelInterface::class);
        $kernel->expects(self::once())->method('boot');

        self::assertSame($kernel, IsolatedKernel::boot(static fn () => $kernel)->kernel);
    }

    /**
     * Like `KernelTestCase::ensureKernelShutdown()` does for the test case's kernel.
     *
     * @test
     */
    #[Test]
    public function it_resets_the_services_tagged_for_reset_after_the_shutdown(): void
    {
        $calls = new \ArrayObject();
        $container = new Container();
        $container->set('services_resetter', new class($calls) implements ResetInterface {
            public function __construct(private readonly \ArrayObject $calls)
            {
            }

            public function reset(): void
            {
                $this->calls[] = 'reset';
            }
        });
        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('getContainer')->willReturn($container);
        $kernel->expects(self::once())->method('shutdown')->willReturnCallback(static function () use ($calls) {
            $calls[] = 'shutdown';
        });

        IsolatedKernel::boot(static fn () => $kernel)->shutdown();

        self::assertSame(['shutdown', 'reset'], $calls->getArrayCopy());
    }

    /**
     * Regression for issue 60: `Pimcore\Test\KernelTestCase::createKernel()` registers every new kernel as Pimcore's
     * static one, which must not be left pointing to a kernel after its shutdown.
     *
     * @test
     */
    #[Test]
    public function it_points_pimcore_back_to_the_previous_kernel_only_after_the_shutdown(): void
    {
        $previousKernel = $this->createStub(KernelInterface::class);
        $ownKernel = $this->createMock(KernelInterface::class);
        $ownKernel->expects(self::once())->method('shutdown')->willReturnCallback(
            // Pimcore's `Kernel::shutdown()` needs its static kernel to still be the one shutting down.
            static fn () => self::assertSame($ownKernel, \Pimcore::getKernel()),
        );

        \Pimcore::setKernel($previousKernel);
        $kernel = IsolatedKernel::boot(static function () use ($ownKernel) {
            \Pimcore::setKernel($ownKernel);

            return $ownKernel;
        });

        self::assertSame($ownKernel, \Pimcore::getKernel());

        $kernel->shutdown();

        self::assertSame($previousKernel, \Pimcore::getKernel());
    }

    /** @test */
    #[Test]
    public function it_points_pimcore_back_to_the_previous_kernel_when_booting_fails(): void
    {
        $previousKernel = $this->createStub(KernelInterface::class);
        $ownKernel = $this->createMock(KernelInterface::class);
        $ownKernel->expects(self::once())->method('boot')->willThrowException(new \RuntimeException('boot failed'));

        \Pimcore::setKernel($previousKernel);

        try {
            IsolatedKernel::boot(static function () use ($ownKernel) {
                \Pimcore::setKernel($ownKernel);

                return $ownKernel;
            });
            self::fail('boot() should have propagated the failure');
        } catch (\RuntimeException $e) {
            self::assertSame('boot failed', $e->getMessage());
        }

        self::assertSame($previousKernel, \Pimcore::getKernel());
    }
}
