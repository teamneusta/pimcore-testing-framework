<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework;

use Neusta\Pimcore\TestingFramework\Exception\DoesNotExtendKernelTestCase;
use Neusta\Pimcore\TestingFramework\Internal\KernelConfigurator;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @mixin KernelTestCase
 */
trait ConfigurableKernel
{
    /**
     * @param array{config?: callable(TestKernel):void, environment?: string, debug?: bool, ...} $options
     */
    protected static function createKernel(array $options = []): TestKernel
    {
        self::assertUsesKernelTestCase();

        $kernel = parent::createKernel($options);

        if (!$kernel instanceof TestKernel) {
            throw new \LogicException(\sprintf('Kernel must be an instance of %s', TestKernel::class));
        }

        KernelConfigurator::apply($kernel);

        $kernel->handleOptions($options);

        return $kernel;
    }

    /**
     * @internal
     *
     * @before
     */
    #[Before]
    public function _collectKernelConfigurations(): void
    {
        self::assertUsesKernelTestCase();

        KernelConfigurator::collect($this);
    }

    /**
     * @internal
     *
     * @after
     */
    #[After]
    public function _resetKernelConfigurations(): void
    {
        KernelConfigurator::reset();
    }

    private static function assertUsesKernelTestCase(): void
    {
        if (!is_subclass_of(static::class, KernelTestCase::class)) {
            throw DoesNotExtendKernelTestCase::forTrait(__TRAIT__);
        }
    }
}
