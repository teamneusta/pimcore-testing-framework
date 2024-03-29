<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Unit\Pimcore;

use Neusta\Pimcore\TestingFramework\Pimcore\WithoutInheritedValues;
use PHPUnit\Framework\TestCase;
use Pimcore\Model\DataObject;

class WithoutInheritedValuesTest extends TestCase
{
    use WithoutInheritedValues;

    /**
     * @test
     */
    public function it_disables_inherited_values(): void
    {
        self::assertFalse(DataObject::getGetInheritedValues());
    }
}
