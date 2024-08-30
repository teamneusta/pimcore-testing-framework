<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Attribute\Kernel;

use Neusta\Pimcore\TestingFramework\Attribute\ConfigureKernel;
use Neusta\Pimcore\TestingFramework\TestKernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class ConfigureContainer implements ConfigureKernel
{
    /**
     * @param string|\Closure(ContainerBuilder):void $config path to a config file or a closure which gets the {@see ContainerBuilder} as its first argument
     */
    public function __construct(
        private readonly string|\Closure $config,
    ) {
    }

    public function configure(TestKernel $kernel): void
    {
        $kernel->addTestConfig($this->config);
    }
}
