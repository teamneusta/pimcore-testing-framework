<?php

declare(strict_types=1);

use Neusta\Pimcore\TestingFramework\Kernel\TestKernel;
use Neusta\Pimcore\TestingFramework\Pimcore\BootstrapPimcore;
use Symfony\Component\ErrorHandler\ErrorHandler;

include dirname(__DIR__) . '/vendor/autoload.php';

// This fixes PHPUnit's complaint: "Test code or tested code did not remove its own exception handlers"
// See: https://github.com/symfony/symfony/issues/53812#issuecomment-1962740145
set_exception_handler([new ErrorHandler(), 'handleException']);

BootstrapPimcore::bootstrap(
    PIMCORE_PROJECT_ROOT: __DIR__ . '/app',
    KERNEL_CLASS: TestKernel::class,
);
