<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Pimcore;

use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\DataObjectInheritance;
use Pimcore\Model\DataObject;

trigger_deprecation(
    'teamneusta/pimcore-testing-framework',
    '0.13',
    'The "%s" trait is deprecated, use the "#[%s(enable: false)]" attribute instead.',
    WithoutInheritedValues::class,
    DataObjectInheritance::class,
);

/**
 * @deprecated since 0.13, use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\Inheritance instead
 */
trait WithoutInheritedValues
{
    /** @internal */
    private static bool $inheritedValuesBackup;

    /**
     * @internal
     *
     * @beforeClass
     */
    public static function _disableInheritedValues(): void
    {
        self::$inheritedValuesBackup = DataObject::getGetInheritedValues();
        DataObject::setGetInheritedValues(false);
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
