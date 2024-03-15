<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Unit\Pimcore;

use Neusta\Pimcore\TestingFramework\Pimcore\WithInheritedValues;
use PHPUnit\Framework\TestCase;
use Pimcore\Model\DataObject;

class WithInheritedValuesTest extends TestCase
{
    use WithInheritedValues;

    /**
     * @test
     */
    public function it_enables_inherited_values(): void
    {
        self::assertTrue(DataObject::getGetInheritedValues());
    }
}
