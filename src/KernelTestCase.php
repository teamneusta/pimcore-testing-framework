<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework;

use Neusta\Pimcore\TestingFramework\Internal\ConfigureKernel;

abstract class KernelTestCase extends \Pimcore\Test\KernelTestCase
{
    use ConfigureKernel;
}
