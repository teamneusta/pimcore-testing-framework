<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Test\Attribute;

use Neusta\Pimcore\TestingFramework\Kernel\TestKernel;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class ConfigureContainer implements KernelConfiguration
{
    /**
     * @param string $config path to a config file
     */
    public function __construct(
        private readonly string $config,
    ) {
    }

    public function configure(TestKernel $kernel): void
    {
        $kernel->addTestConfig($this->config);
    }
}
