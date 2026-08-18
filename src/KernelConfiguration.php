<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework;

interface KernelConfiguration
{
    public function configure(TestKernel $kernel): void;
}
