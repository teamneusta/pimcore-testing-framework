<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Pimcore;

use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Localizedfield;
use Pimcore\Model\Document;

final class AdminMode
{
    public static function enable(): void
    {
        \Pimcore::setAdminMode();
        Document::setHideUnpublished(false);
        DataObject::setHideUnpublished(false);
        DataObject::setGetInheritedValues(false);
        Localizedfield::setGetFallbackValues(false);
    }

    public static function disable(): void
    {
        \Pimcore::unsetAdminMode();
        Document::setHideUnpublished(true);
        DataObject::setHideUnpublished(true);
        DataObject::setGetInheritedValues(true);
        Localizedfield::setGetFallbackValues(true);
    }

    public static function isEnabled(): bool
    {
        return \Pimcore::inAdmin();
    }
}
