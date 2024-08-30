<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Test\Attribute;

use Neusta\Pimcore\TestingFramework\TestKernel;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class RegisterCompilerPass implements KernelConfiguration
{
    /**
     * @param PassConfig::TYPE_* $type
     */
    public function __construct(
        private readonly CompilerPassInterface $compilerPass,
        private readonly string $type = PassConfig::TYPE_BEFORE_OPTIMIZATION,
        private readonly int $priority = 0,
    ) {
    }

    public function configure(TestKernel $kernel): void
    {
        $kernel->addTestCompilerPass($this->compilerPass, $this->type, $this->priority);
    }
}
