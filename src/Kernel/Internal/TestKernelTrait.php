<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Kernel\Internal;

use Pimcore\Kernel;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 *
 * @mixin Kernel
 */
trait TestKernelTrait
{
    private bool $dynamicCache = false;
    /** @var array<string, array> */
    private array $testExtensionConfigs = [];

    public function addTestExtensionConfig(string $namespace, array $config): void
    {
        $this->testExtensionConfigs[$namespace] = $config;
        $this->dynamicCache = true;
    }

    public function handleOptions(array $options): void
    {
        if (\is_callable($configure = $options['config'] ?? null)) {
            $configure($this);
        }
    }

    public function getCacheDir(): string
    {
        if ($this->dynamicCache) {
            return rtrim(parent::getCacheDir(), '/') . '/' . spl_object_hash($this);
        }

        return parent::getCacheDir();
    }

    public function shutdown(): void
    {
        parent::shutdown();

        if ($this->dynamicCache) {
            (new Filesystem())->remove($this->getCacheDir());
        }
    }
}
