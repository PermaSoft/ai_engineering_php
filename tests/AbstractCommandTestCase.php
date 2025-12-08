<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Base class for testing Symfony Console commands.
 *
 * Provides helper methods for:
 * - Executing commands
 * - Checking output
 * - Testing interactive and non-interactive modes
 */
abstract class AbstractCommandTestCase extends KernelTestCase
{
    protected Application $application;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->application = new Application(self::$kernel);
    }

    /**
     * Executes a command and returns its tester.
     *
     * @param array<string, mixed> $input Command input arguments and options
     * @param array<string, mixed> $options CommandTester options (interactive, decorated, etc.)
     */
    protected function executeCommand(
        Command $command,
        array $input = [],
        array $options = []
    ): CommandTester {
        $this->application->add($command);

        $commandTester = new CommandTester($command);
        $commandTester->execute($input, $options);

        return $commandTester;
    }

    /**
     * Executes a command by name and returns its tester.
     *
     * @param array<string, mixed> $input Command input arguments and options
     * @param array<string, mixed> $options CommandTester options
     */
    protected function executeCommandByName(
        string $commandName,
        array $input = [],
        array $options = []
    ): CommandTester {
        $command = $this->application->find($commandName);
        $commandTester = new CommandTester($command);
        $commandTester->execute($input, $options);

        return $commandTester;
    }

    /**
     * Asserts that command output contains a specific string.
     */
    protected function assertOutputContains(CommandTester $tester, string $expected): void
    {
        $output = $tester->getDisplay();
        $this->assertStringContainsString($expected, $output);
    }

    /**
     * Asserts that command output does not contain a specific string.
     */
    protected function assertOutputNotContains(CommandTester $tester, string $expected): void
    {
        $output = $tester->getDisplay();
        $this->assertStringNotContainsString($expected, $output);
    }

    /**
     * Asserts that command exited successfully.
     */
    protected function assertCommandIsSuccessful(CommandTester $tester): void
    {
        $this->assertSame(
            Command::SUCCESS,
            $tester->getStatusCode(),
            sprintf('Command failed with output: %s', $tester->getDisplay())
        );
    }

    /**
     * Asserts that command failed.
     */
    protected function assertCommandFailed(CommandTester $tester): void
    {
        $this->assertNotSame(
            Command::SUCCESS,
            $tester->getStatusCode(),
            'Command should have failed but succeeded'
        );
    }
}
