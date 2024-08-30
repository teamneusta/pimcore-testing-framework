<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Attribute;

use Neusta\Pimcore\TestingFramework\TestKernel;

interface ConfigureKernel
{
    public function configure(TestKernel $kernel): void;
}
