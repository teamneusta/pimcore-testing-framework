<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Test\Attribute;

use Neusta\Pimcore\TestingFramework\TestKernel;

interface KernelConfiguration
{
    public function configure(TestKernel $kernel): void;
}
