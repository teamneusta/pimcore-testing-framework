<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Exception;

final class KernelIsNotBooted extends \LogicException
{
    /**
     * @param class-string $attribute
     */
    public static function forAttribute(string $attribute): self
    {
        return new self(\sprintf(
            'The "%s" attribute requires a booted kernel.',
            $attribute,
        ));
    }
}
