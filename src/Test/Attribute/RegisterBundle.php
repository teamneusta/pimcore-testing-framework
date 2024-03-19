<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Test\Attribute;

use Neusta\Pimcore\TestingFramework\Kernel\TestKernel;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class RegisterBundle implements KernelConfiguration
{
    /**
     * @param class-string<BundleInterface> $bundle
     */
    public function __construct(
        private readonly string $bundle,
    ) {
    }

    public function configure(TestKernel $kernel): void
    {
        $kernel->addTestBundle($this->bundle);
    }
}
