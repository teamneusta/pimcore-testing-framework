<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Test;

use Neusta\Pimcore\TestingFramework\Kernel\TestKernel;
use Pimcore\Test\KernelTestCase;

abstract class ConfigurableKernelTestCase extends KernelTestCase
{
    /**
     * @param array<mixed> $options
     */
    protected static function createKernel(array $options = []): TestKernel
    {
        $kernel = parent::createKernel($options);
        \assert($kernel instanceof TestKernel);

        $kernel->handleOptions($options);

        return $kernel;
    }
}
