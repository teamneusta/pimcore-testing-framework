<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Fixtures\NotAKernelTestCase;

use Neusta\Pimcore\TestingFramework\ResetDatabase;

final class UsingResetDatabase
{
    use ResetDatabase;

    public function resetSchema(): void
    {
        $this->_resetSchema();
    }
}
