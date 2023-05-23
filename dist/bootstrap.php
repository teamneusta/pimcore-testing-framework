<?php declare(strict_types=1);

use Neusta\Pimcore\TestingFramework\Pimcore\AdminMode;
use Neusta\Pimcore\TestingFramework\Pimcore\BootstrapPimcore;

include __DIR__ . '/../../../../vendor/autoload.php';

BootstrapPimcore::setEnv('PIMCORE_PROJECT_ROOT', __DIR__ . '/../../../../tests/app');
BootstrapPimcore::setEnv('KERNEL_CLASS', TestKernel::class);
BootstrapPimcore::bootstrap();
AdminMode::disable();
