<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Attribute\Pimcore;

use Neusta\Pimcore\TestingFramework\Attribute\ConfigurePimcore;
use Pimcore\Model\DataObject;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
final class Inheritance implements ConfigurePimcore
{
    private static bool $inheritedValuesBackup;

    public static function requiresBootedKernel(): bool
    {
        return false;
    }

    public function __construct(
        private readonly bool $enable,
    ) {
    }

    public function apply(): void
    {
        self::$inheritedValuesBackup = DataObject::getGetInheritedValues();

        DataObject::setGetInheritedValues($this->enable);
    }

    public function reset(): void
    {
        DataObject::setGetInheritedValues(self::$inheritedValuesBackup);
    }
}
