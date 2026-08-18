<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Fixtures\PimcoreConfiguration;

use PHPUnit\Framework\TestCase;

/**
 * Not a test itself - the parent half of a hierarchy used to check that `AttributeProvider` walks up
 * the parent chain and orders parents before children.
 */
#[RecordingConfiguration('parent')]
abstract class InheritedTestCaseParent extends TestCase
{
}
