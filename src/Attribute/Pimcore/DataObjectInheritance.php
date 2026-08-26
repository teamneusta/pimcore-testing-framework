<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Attribute\Pimcore;

use Neusta\Pimcore\TestingFramework\PimcoreConfiguration;
use Pimcore\Model\DataObject;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
final class DataObjectInheritance implements PimcoreConfiguration
{
    private bool $inheritedValuesBackup;

    public static function requiresBootedKernel(): bool
    {
        return false;
    }

    public function __construct(
        private readonly bool $enable = true,
    ) {
    }

    public function apply(): void
    {
        $this->inheritedValuesBackup = DataObject::getGetInheritedValues();

        DataObject::setGetInheritedValues($this->enable);
    }

    public function reset(): void
    {
        DataObject::setGetInheritedValues($this->inheritedValuesBackup);
    }
}
