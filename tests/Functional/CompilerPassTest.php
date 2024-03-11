<?php
declare(strict_types=1);

namespace Functional;

use Fixtures\ConfigurationBundle\DependencyInjection\Compiler\DeregisterSomethingPass;
use Fixtures\ConfigurationBundle\DependencyInjection\Compiler\RegisterSomethingPass;
use Neusta\Pimcore\TestingFramework\Kernel\TestKernel;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableKernelTestCase;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

final class CompilerPassTest extends ConfigurableKernelTestCase
{
    /**
     * @test
     */
    public function compiler_pass_priority(): void
    {
        // Case 1: Compiler pass without priority - it should be prioritized by order of addition
        self::bootKernel(['config' => function (TestKernel $kernel) {
            $kernel->addTestCompilerPass(new DeregisterSomethingPass());
            $kernel->addTestCompilerPass(new RegisterSomethingPass());
        }]);

        $this->assertTrue(self::getContainer()->has('something'));

        // Case 2: Compiler pass with priority - it should be prioritized by priority
        self::bootKernel(['config' => function (TestKernel $kernel) {
            $kernel->addTestCompilerPass(new DeregisterSomethingPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -5);
            $kernel->addTestCompilerPass(new RegisterSomethingPass());
        }]);

        $this->assertFalse(self::getContainer()->has('something'));

        // Case 3: Compiler pass without priority - it should be prioritized by order of addition
        self::bootKernel(['config' => function (TestKernel $kernel) {
            // DeregisterSomethingPass is now added as second compiler pass
            $kernel->addTestCompilerPass(new RegisterSomethingPass());
            $kernel->addTestCompilerPass(new DeregisterSomethingPass());
        }]);

        $this->assertFalse(self::getContainer()->has('something'));
    }
}
