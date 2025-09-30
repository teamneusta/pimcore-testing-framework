<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Pimcore;

use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\DataObjectInheritance;
use Pimcore\Model\DataObject;

trigger_deprecation(
    'teamneusta/pimcore-testing-framework',
    '0.13',
    'The "%s" trait is deprecated, use the "#[%s(enable: true)]" attribute instead.',
    WithInheritedValues::class,
    DataObjectInheritance::class,
);

/**
 * @deprecated since 0.13, use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\Inheritance instead
 */
trait WithInheritedValues
{
    /** @internal */
    private static bool $inheritedValuesBackup;

    /**
     * @internal
     *
     * @beforeClass
     */
    public static function _enableInheritedValues(): void
    {
        self::$inheritedValuesBackup = DataObject::getGetInheritedValues();
        DataObject::setGetInheritedValues(true);
    }

    /**
     * @internal
     *
     * @afterClass
     */
    public static function _resetInheritedValues(): void
    {
        DataObject::setGetInheritedValues(self::$inheritedValuesBackup);
    }
}
