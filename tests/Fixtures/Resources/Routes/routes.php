<?php declare(strict_types=1);

use Neusta\Pimcore\TestingFramework\Tests\Fixtures\Controller\ExampleController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->add('example_route', '/example')
        ->controller(ExampleController::class)
    ;
};
