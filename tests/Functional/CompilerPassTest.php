<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Functional;

use Neusta\Pimcore\TestingFramework\Attribute\Kernel\RegisterCompilerPass;
use Neusta\Pimcore\TestingFramework\KernelTestCase;
use Neusta\Pimcore\TestingFramework\TestKernel;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\ConfigurationBundle\DependencyInjection\Compiler\DeregisterSomethingPass;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\ConfigurationBundle\DependencyInjection\Compiler\RegisterSomethingPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

final class CompilerPassTest extends KernelTestCase
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

        self::assertTrue(self::getContainer()->has('something'));

        // Case 2: Compiler pass with priority - it should be prioritized by priority
        self::bootKernel(['config' => function (TestKernel $kernel) {
            $kernel->addTestCompilerPass(new DeregisterSomethingPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -5);
            $kernel->addTestCompilerPass(new RegisterSomethingPass());
        }]);

        self::assertFalse(self::getContainer()->has('something'));

        // Case 3: Compiler pass without priority - it should be prioritized by order of addition
        self::bootKernel(['config' => function (TestKernel $kernel) {
            // DeregisterSomethingPass is now added as second compiler pass
            $kernel->addTestCompilerPass(new RegisterSomethingPass());
            $kernel->addTestCompilerPass(new DeregisterSomethingPass());
        }]);

        self::assertFalse(self::getContainer()->has('something'));
    }

    /**
     * @test
     */
    #[RegisterCompilerPass(new DeregisterSomethingPass())]
    #[RegisterCompilerPass(new RegisterSomethingPass())]
    public function compiler_passes_via_attributes(): void
    {
        $this->assertTrue(self::getContainer()->has('something'));
    }

    /**
     * @test
     */
    #[RegisterCompilerPass(new DeregisterSomethingPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -5)]
    #[RegisterCompilerPass(new RegisterSomethingPass())]
    public function compiler_passes_with_priority_via_attributes(): void
    {
        $this->assertFalse(self::getContainer()->has('something'));
    }

    /**
     * @test
     */
    #[RegisterCompilerPass(new RegisterSomethingPass())]
    #[RegisterCompilerPass(new DeregisterSomethingPass())]
    public function compiler_passes_without_priority_via_attributes(): void
    {
        $this->assertFalse(self::getContainer()->has('something'));
    }
}
