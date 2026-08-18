<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Pimcore;

use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\DataObjectInheritance;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\BeforeClass;
use Pimcore\Model\DataObject;

trigger_deprecation(
    'teamneusta/pimcore-testing-framework',
    '0.15',
    'The "%s" trait is deprecated, use the "#[%s(enable: false)]" attribute instead.',
    WithoutInheritedValues::class,
    DataObjectInheritance::class,
);

/**
 * @deprecated since 0.15, use Neusta\Pimcore\TestingFramework\Attribute\Pimcore\DataObjectInheritance instead
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
    #[BeforeClass]
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
    #[AfterClass]
    public static function _resetInheritedValues(): void
    {
        DataObject::setGetInheritedValues(self::$inheritedValuesBackup);
    }
}
