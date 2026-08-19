<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Functional;

use Neusta\Pimcore\TestingFramework\Test\Attribute\ConfigureRoute;
use Neusta\Pimcore\TestingFramework\Test\ConfigurableKernelTestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Routing\Route;

#[ConfigureRoute(__DIR__ . '/../Fixtures/Resources/Routes/routes.php')]
final class ConfigurableKernelTestcaseTest extends ConfigurableKernelTestCase
{
    /** @test */
    #[Test]
    public function it_configures_the_kernel_via_attributes(): void
    {
        self::bootKernel();

        $route = self::getContainer()->get('router')->getRouteCollection()->get('example_route');

        self::assertInstanceOf(Route::class, $route);
        self::assertSame('/example', $route->getPath());
    }
}
