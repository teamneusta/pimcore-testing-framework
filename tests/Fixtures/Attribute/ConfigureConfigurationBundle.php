<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Fixtures\Attribute;

use Neusta\Pimcore\TestingFramework\Test\Attribute\KernelConfiguration;
use Neusta\Pimcore\TestingFramework\TestKernel;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\ConfigurationBundle\ConfigurationBundle;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
final class ConfigureConfigurationBundle implements KernelConfiguration
{
    public function __construct(
        private readonly array $config,
    ) {
    }

    public function configure(TestKernel $kernel): void
    {
        $kernel->addTestBundle(ConfigurationBundle::class);
        $kernel->addTestExtensionConfig('configuration', array_merge(
            ['bar' => ['value2', 'value3']],
            $this->config,
        ));
    }
}
