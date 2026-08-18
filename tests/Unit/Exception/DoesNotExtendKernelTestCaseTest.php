<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Unit\Exception;

use Neusta\Pimcore\TestingFramework\Exception\DoesNotExtendKernelTestCase;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\NotAKernelTestCase\UsingResetDatabase;
use Neusta\Pimcore\TestingFramework\Tests\Fixtures\NotAKernelTestCase\UsingWithoutCache;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoesNotExtendKernelTestCaseTest extends TestCase
{
    /** @test */
    #[Test]
    public function it_builds_the_expected_message(): void
    {
        $exception = DoesNotExtendKernelTestCase::forTrait('SomeTrait');

        self::assertSame(
            \sprintf(
                'The trait "SomeTrait" can only be used on TestCases that extend "%s".',
                KernelTestCase::class,
            ),
            $exception->getMessage(),
        );
    }

    /** @test */
    #[Test]
    public function reset_database_guards_against_missing_kernel_test_case(): void
    {
        $this->expectException(DoesNotExtendKernelTestCase::class);

        (new UsingResetDatabase())->resetSchema();
    }

    /** @test */
    #[Test]
    public function without_cache_guards_against_missing_kernel_test_case(): void
    {
        $this->expectException(DoesNotExtendKernelTestCase::class);

        UsingWithoutCache::boot();
    }
}
