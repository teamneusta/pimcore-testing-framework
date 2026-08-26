<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Fixtures\PimcoreConfiguration;

use Neusta\Pimcore\TestingFramework\PimcoreConfiguration;

/**
 * Records which configurators were applied and reset, so tests can assert the order and - more
 * importantly - that `reset()` never runs for a configurator whose `apply()` did not complete.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class RecordingConfiguration implements PimcoreConfiguration
{
    /** @var list<string> */
    public static array $applied = [];
    /** @var list<string> */
    public static array $reset = [];

    private static bool $needsKernel = false;

    public function __construct(
        private readonly string $id,
        private readonly bool $failOnApply = false,
    ) {
    }

    public static function needsKernel(bool $needsKernel): void
    {
        self::$needsKernel = $needsKernel;
    }

    public static function forget(): void
    {
        self::$applied = [];
        self::$reset = [];
        self::$needsKernel = false;
    }

    public static function requiresBootedKernel(): bool
    {
        return self::$needsKernel;
    }

    public function apply(): void
    {
        if ($this->failOnApply) {
            throw new \RuntimeException("apply() failed for {$this->id}");
        }

        self::$applied[] = $this->id;
    }

    public function reset(): void
    {
        self::$reset[] = $this->id;
    }
}
