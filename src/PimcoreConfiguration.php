<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework;

interface PimcoreConfiguration
{
    public static function requiresBootedKernel(): bool;

    public function apply(): void;

    public function reset(): void;
}
