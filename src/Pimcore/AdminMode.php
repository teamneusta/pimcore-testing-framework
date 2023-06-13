<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Pimcore;

use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Localizedfield;
use Pimcore\Model\Document;

final class AdminMode
{
    public static function enable(): void
    {
        \Pimcore::setAdminMode();
        Document::setHideUnpublished(false);
        AbstractObject::setHideUnpublished(false);
        AbstractObject::setGetInheritedValues(false);
        Localizedfield::setGetFallbackValues(false);
    }

    public static function disable(): void
    {
        \Pimcore::unsetAdminMode();
        Document::setHideUnpublished(true);
        AbstractObject::setHideUnpublished(true);
        AbstractObject::setGetInheritedValues(true);
        Localizedfield::setGetFallbackValues(true);
    }
}
