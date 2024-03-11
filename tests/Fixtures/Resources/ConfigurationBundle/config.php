<?php declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $container) {
    $container->extension('configuration', [
        'foo' => 'value1',
        'bar' => ['value2', 'value3'],
    ]);

    $container->services()->set('something', stdClass::class)->public();
};
