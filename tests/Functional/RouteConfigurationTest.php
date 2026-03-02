<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Functional;

use Neusta\Pimcore\TestingFramework\Kernel\TestKernel;
use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureRoute;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableKernelTestCase;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\Controller\ExampleController;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Route;

final class RouteConfigurationTest extends ConfigurableKernelTestCase
{
    public static function provideDifferentConfigurationFormats(): iterable
    {
        yield 'YAML' => [__DIR__ . '/../Fixtures/Resources/Routes/routes.yaml'];
        yield 'XML' => [__DIR__ . '/../Fixtures/Resources/Routes/routes.xml'];
        yield 'PHP' => [__DIR__ . '/../Fixtures/Resources/Routes/routes.php'];
        yield 'Callable' => [static function (RoutingConfigurator $routes): void {
            $routes->add('example_route', '/example')->controller(ExampleController::class);
        }];
    }

    #[Test]
    #[DataProvider('provideDifferentConfigurationFormats')]
    public function different_configuration_formats(string|callable $config): void
    {
        self::bootKernel(['config' => static fn (TestKernel $kernel) => $kernel->addTestRoute($config)]);

        self::assertRouteConfiguration(self::getContainer());
    }

    public static function provideDifferentConfigurationFormatsViaKernelConfigurationObject(): iterable
    {
        yield 'YAML' => [new ConfigureRoute(__DIR__ . '/../Fixtures/Resources/Routes/routes.yaml')];
        yield 'XML' => [new ConfigureRoute(__DIR__ . '/../Fixtures/Resources/Routes/routes.xml')];
        yield 'PHP' => [new ConfigureRoute(__DIR__ . '/../Fixtures/Resources/Routes/routes.php')];
        yield 'Callable' => [new ConfigureRoute(static function (RoutingConfigurator $routes): void {
            $routes->add('example_route', '/example')->controller(ExampleController::class);
        })];
    }

    #[Test]
    #[DataProvider('provideDifferentConfigurationFormatsViaKernelConfigurationObject')]
    public function different_configuration_formats_via_data_provider(): void
    {
        self::assertRouteConfiguration(self::getContainer());
    }

    #[Test]
    #[ConfigureRoute(__DIR__ . '/../Fixtures/Resources/Routes/routes.yaml')]
    public function configuration_in_yaml_via_attribute(): void
    {
        self::assertRouteConfiguration(self::getContainer());
    }

    #[Test]
    #[ConfigureRoute(__DIR__ . '/../Fixtures/Resources/Routes/routes.xml')]
    public function configuration_in_xml_via_attribute(): void
    {
        self::assertRouteConfiguration(self::getContainer());
    }

    #[Test]
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
