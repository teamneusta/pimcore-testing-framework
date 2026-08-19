<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Unit;

use Neusta\Pimcore\TestingFramework\BootstrapPimcore;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * `bootstrap()` itself isn't covered here - it calls Pimcore's own bootstrap process, which this
 * project's tests/bootstrap.php already runs once for the whole suite; invoking it a second time isn't
 * a simple, side-effect-free thing to do. `setEnv()` is the standalone, side-effect-visible building
 * block it and consumers rely on, and is safe to exercise directly.
 */
final class BootstrapPimcoreTest extends TestCase
{
    private const NAME = 'NEUSTA_TESTING_FRAMEWORK_BOOTSTRAP_TEST';

    protected function tearDown(): void
    {
        putenv(self::NAME);
        unset($_ENV[self::NAME], $_SERVER[self::NAME]);
    }

    /** @test */
    #[Test]
    public function it_sets_the_environment_variable_in_every_place_pimcore_reads_it_from(): void
    {
        BootstrapPimcore::setEnv(self::NAME, 'some-value');

        self::assertSame('some-value', getenv(self::NAME));
        self::assertSame('some-value', $_ENV[self::NAME]);
        self::assertSame('some-value', $_SERVER[self::NAME]);
    }

    /** @test */
    #[Test]
    public function it_overwrites_a_previously_set_value(): void
    {
        BootstrapPimcore::setEnv(self::NAME, 'first');
        BootstrapPimcore::setEnv(self::NAME, 'second');

        self::assertSame('second', getenv(self::NAME));
        self::assertSame('second', $_ENV[self::NAME]);
        self::assertSame('second', $_SERVER[self::NAME]);
    }
}
