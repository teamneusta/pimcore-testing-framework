<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Exception;

use Pimcore\Test\KernelTestCase;

final class DoesNotExtendKernelTestCase extends \RuntimeException
{
    public static function forTrait(string $trait): self
    {
        return new self(\sprintf(
            'The trait "%s" can only be used on TestCases that extend "%s".',
            $trait,
            KernelTestCase::class,
        ));
    }
}
