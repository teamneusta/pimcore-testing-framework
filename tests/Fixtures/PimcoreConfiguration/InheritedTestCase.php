<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Fixtures\PimcoreConfiguration;

/**
 * Not a test itself - the child half of the hierarchy, see {@see InheritedTestCaseParent}.
 */
#[RecordingConfiguration('child')]
final class InheritedTestCase extends InheritedTestCaseParent
{
    public function inherited(): void
    {
    }

    #[RecordingConfiguration('method')]
    public function inherited_with_method_attribute(): void
    {
    }
}
