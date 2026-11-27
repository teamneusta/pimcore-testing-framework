<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Internal;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * A kernel booted for the framework's own work (resetting the database, applying Pimcore configurations), independent
 * of the one the test case boots: it never touches `KernelTestCase::$kernel`, and it leaves Pimcore's static kernel as
 * it found it.
 *
 * `Pimcore\Test\KernelTestCase::createKernel()` registers every new kernel as Pimcore's static one. Left pointing to
 * this kernel after its shutdown, Pimcore would resolve its container through a dead kernel while the test boots or
 * still uses another one (see issue 60).
 *
 * @internal
 */
final class IsolatedKernel
{
    private function __construct(
        public readonly KernelInterface $kernel,
        private readonly ?KernelInterface $previousPimcoreKernel,
    ) {
    }

    /**
     * @param \Closure(): KernelInterface $createKernel creates a new, not yet booted kernel
     */
    public static function boot(\Closure $createKernel): self
    {
        // Before creating the kernel, since that already re-points Pimcore's static kernel.
        $previousPimcoreKernel = \Pimcore::getKernel();
        $kernel = null;

        try {
            $kernel = $createKernel();
            $kernel->boot();
        } catch (\Throwable $e) {
            // A no-op for a kernel that did not finish booting.
            $kernel?->shutdown();
            self::restorePimcoreKernel($previousPimcoreKernel);

            throw $e;
        }

        return new self($kernel, $previousPimcoreKernel);
    }

    /**
     * Mirrors what `KernelTestCase::ensureKernelShutdown()` does for the test case's kernel: resets the services tagged
     * `kernel.reset` as well, in case one of them clears state that outlives the container.
     *
     * The order matters: Pimcore's `Kernel::shutdown()` (and possibly a reset service) resolves services through
     * Pimcore's static kernel, which therefore must still be this one until the very end.
     */
    public function shutdown(): void
    {
        try {
            $container = $this->kernel->getContainer();

            if ($container->has('services_resetter')) {
                // Instantiate it, because `Container::reset()` only resets services that have been used.
                $container->get('services_resetter');
            }

            $this->kernel->shutdown();

            if ($container instanceof Container) {
                $container->reset();
            }
        } finally {
            self::restorePimcoreKernel($this->previousPimcoreKernel);
        }
    }

    private static function restorePimcoreKernel(?KernelInterface $kernel): void
    {
        // `Pimcore::setKernel()` does not accept null; if there was none, the next kernel created replaces it anyway.
        if ($kernel) {
            \Pimcore::setKernel($kernel);
        }
    }
}
