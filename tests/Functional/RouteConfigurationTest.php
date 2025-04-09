<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Functional;

use Neusta\Pimcore\TestingFramework\Kernel\TestKernel;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureRoute;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableKernelTestCase;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\Controller\ExampleController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Route;

final class RouteConfigurationTest extends ConfigurableKernelTestCase
{
    public function provideDifferentConfigurationFormats(): iterable
    {
        yield 'YAML' => [__DIR__ . '/../Fixtures/Resources/Routes/routes.yaml'];
        yield 'XML' => [__DIR__ . '/../Fixtures/Resources/Routes/routes.xml'];
        yield 'PHP' => [__DIR__ . '/../Fixtures/Resources/Routes/routes.php'];
        yield 'Callable' => [function (RoutingConfigurator $routes): void {
            $routes->add('example_route', '/example')->controller(ExampleController::class);
        }];
    }

    /**
     * @test
     *
     * @dataProvider provideDifferentConfigurationFormats
     */
    public function different_configuration_formats(string|callable $config): void
    {
        self::bootKernel(['config' => fn (TestKernel $kernel) => $kernel->addTestRoute($config)]);

        self::assertRouteConfiguration(self::getContainer());
    }

    public function provideDifferentConfigurationFormatsViaKernelConfigurationObject(): iterable
    {
        yield 'YAML' => [new ConfigureRoute(__DIR__ . '/../Fixtures/Resources/Routes/routes.yaml')];
        yield 'XML' => [new ConfigureRoute(__DIR__ . '/../Fixtures/Resources/Routes/routes.xml')];
        yield 'PHP' => [new ConfigureRoute(__DIR__ . '/../Fixtures/Resources/Routes/routes.php')];
        yield 'Callable' => [new ConfigureRoute(function (RoutingConfigurator $routes): void {
            $routes->add('example_route', '/example')->controller(ExampleController::class);
        })];
    }

    /**
     * @test
     *
     * @dataProvider provideDifferentConfigurationFormatsViaKernelConfigurationObject
     */
    public function different_configuration_formats_via_data_provider(): void
    {
        self::assertRouteConfiguration(self::getContainer());
    }

    /**
     * @test
     */
    #[ConfigureRoute(__DIR__ . '/../Fixtures/Resources/Routes/routes.yaml')]
    public function configuration_in_yaml_via_attribute(): void
    {
        self::assertRouteConfiguration(self::getContainer());
    }

    /**
     * @test
     */
    #[ConfigureRoute(__DIR__ . '/../Fixtures/Resources/Routes/routes.xml')]
    public function configuration_in_xml_via_attribute(): void
    {
        self::assertRouteConfiguration(self::getContainer());
    }

    /**
     * @test
     */
    #[ConfigureRoute(__DIR__ . '/../Fixtures/Resources/Routes/routes.php')]
    public function configuration_in_php_via_attribute(): void
    {
        self::assertRouteConfiguration(self::getContainer());
    }

    public static function assertRouteConfiguration(ContainerInterface $container): void
    {
        $route = $container->get('router')->getRouteCollection()->get('example_route');

        self::assertInstanceOf(Route::class, $route);
        self::assertSame('/example', $route->getPath());
    }
}
