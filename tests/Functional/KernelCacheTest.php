<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Functional;

use Neusta\Pimcore\TestingFramework\Kernel\TestKernel;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableKernelTestCase;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\ConfigurationBundle\ConfigurationBundle;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\Controller\ExampleController;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class KernelCacheTest extends ConfigurableKernelTestCase
{
    /**
     * @test
     */
    public function it_does_not_change_the_cache_directory_of_the_standard_kernel(): void
    {
        $kernel = self::bootKernel();

        self::assertSame($kernel->getCacheDir(), (fn () => parent::getCacheDir())->call($kernel));
    }

    /**
     * @test
     */
    public function it_creates_a_distinct_cache_directory_per_test_config(): void
    {
        $cacheDirs = [
            self::bootKernel(['config' => function (TestKernel $kernel) {
                // Trigger the dynamic cache without configuring anything.
                // This is to ensure that the hash changes when the kernel is actually configured.
                (new \ReflectionProperty($kernel, 'dynamicCache'))->setValue($kernel, true);
            }])->getCacheDir(),
            self::bootKernel(['config' => function (TestKernel $kernel) {
                $kernel->addTestExtensionConfig('framework', ['secret' => 'foo']);
            }])->getCacheDir(),
            self::bootKernel(['config' => function (TestKernel $kernel) {
                $kernel->addTestBundle(ConfigurationBundle::class);
                $kernel->addTestExtensionConfig('configuration', [
                    'foo' => 'value1',
                    'bar' => ['value2', 'value3'],
                ]);
            }])->getCacheDir(),
            self::bootKernel(['config' => function (TestKernel $kernel) {
                $kernel->addTestBundle(ConfigurationBundle::class);
                $kernel->addTestConfig(__DIR__ . '/../Fixtures/Resources/ConfigurationBundle/config.yaml');
            }])->getCacheDir(),
            self::bootKernel(['config' => function (TestKernel $kernel) {
                $kernel->addTestConfig(function (ContainerBuilder $container) {
                    $container->register('something', \stdClass::class)->setPublic(true);
                });
            }])->getCacheDir(),
            self::bootKernel(['config' => function (TestKernel $kernel) {
                $kernel->addTestCompilerPass(new class implements CompilerPassInterface {
                    public function process(ContainerBuilder $container): void
                    {
                    }
                });
            }])->getCacheDir(),
            self::bootKernel(['config' => function (TestKernel $kernel) {
                $kernel->addTestRoute(function (RoutingConfigurator $routes): void {
                    $routes->add('example_route', '/example')->controller(ExampleController::class);
                });
            }])->getCacheDir(),
        ];

        self::assertNotContains(self::bootKernel()->getCacheDir(), $cacheDirs, 'Every cache dir should be dynamic.');
        self::assertSame($cacheDirs, array_unique($cacheDirs), 'Every cache dir should be unique.');
    }
}
