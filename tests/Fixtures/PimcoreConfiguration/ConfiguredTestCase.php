<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Fixtures\PimcoreConfiguration;

use PHPUnit\Framework\TestCase;

/**
 * Not a test itself (the file name does not end in `Test.php`, so PHPUnit ignores it) - it only
 * carries attributes for `AttributeProvider`/`PimcoreConfigurator` to pick up via reflection.
 */
#[RecordingConfiguration('class')]
final class ConfiguredTestCase extends TestCase
{
    public function only_class_level(): void
    {
    }

    #[RecordingConfiguration('method')]
    public function class_and_method_level(): void
    {
    }

    #[RecordingConfiguration('method', failOnApply: true)]
    public function method_level_fails(): void
    {
    }
}
