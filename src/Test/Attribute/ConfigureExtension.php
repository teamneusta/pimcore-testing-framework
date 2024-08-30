<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Test\Attribute;

use Neusta\Pimcore\TestingFramework\TestKernel;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class ConfigureExtension implements KernelConfiguration
{
    /**
     * @param array<string, mixed> $extensionConfig
     */
    public function __construct(
        private readonly string $namespace,
        private readonly array $extensionConfig,
    ) {
    }

    public function configure(TestKernel $kernel): void
    {
        $kernel->addTestExtensionConfig($this->namespace, $this->extensionConfig);
    }
}
