<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Pimcore;

use Pimcore\Model\DataObject;

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
