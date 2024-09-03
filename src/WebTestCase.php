<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework;

use Neusta\Pimcore\TestingFramework\Internal\ConfigureKernel;

abstract class WebTestCase extends \Pimcore\Test\WebTestCase
{
    use ConfigureKernel;
}
