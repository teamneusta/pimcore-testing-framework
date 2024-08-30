<?php
declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Attribute;

interface ConfigurePimcore
{
    public static function requiresBootedKernel(): bool;

    public function apply(): void;

    public function reset(): void;
}
