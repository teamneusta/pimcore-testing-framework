<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Database;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @internal
 */
final class RunCommand
{
    private Application $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function __invoke(string $command, array $parameters = []): void
    {
        $input = new ArrayInput(array_merge(['command' => $command], $parameters));
        $output = new BufferedOutput();
        $exit = $this->application->run($input, $output);

        if (0 !== $exit) {
            throw new \RuntimeException(\sprintf('Error running "%s": %s', $command, $output->fetch()));
        }
    }
}
