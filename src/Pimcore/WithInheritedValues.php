<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Pimcore;

use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\BeforeClass;
use Pimcore\Model\DataObject;

trait WithInheritedValues
{
    /** @internal */
    private static bool $inheritedValuesBackup;

    /** @internal */
    #[BeforeClass]
    public static function _enableInheritedValues(): void
    {
        self::$inheritedValuesBackup = DataObject::getGetInheritedValues();
        DataObject::setGetInheritedValues(true);
    }

    /** @internal */
    #[AfterClass]
    public static function _resetInheritedValues(): void
    {
        DataObject::setGetInheritedValues(self::$inheritedValuesBackup);
    }
}
