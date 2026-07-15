<?php

declare(strict_types=1);

namespace Neusta\Pimcore\TestingFramework\Tests\Functional;

use Neusta\Pimcore\TestingFramework\Database\RunCommand;
use PHPUnit\Framework\Attributes\Test;
use Pimcore\Console\Application;
use Pimcore\Test\KernelTestCase;

final class RunCommandTest extends KernelTestCase
{
    /** @test */
    #[Test]
    public function it_runs_a_successful_command(): void
    {
        $this->createRunCommand()('list');

        $this->expectNotToPerformAssertions();
    }

    /** @test */
    #[Test]
    public function it_throws_for_a_failing_command(): void
    {
        $runCommand = $this->createRunCommand();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/this-command-does-not-exist/');

        $runCommand('this-command-does-not-exist');
    }

    private function createRunCommand(): RunCommand
    {
        $application = new Application(self::bootKernel());
        $application->setAutoExit(false);

        return new RunCommand($application);
    }
}
