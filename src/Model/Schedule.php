<?php

namespace Adamski\Symfony\ScheduleBundle\Model;

use InvalidArgumentException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Lock\LockFactory;

class Schedule {

    /**
     * @param SymfonyStyle    $console
     * @param Application     $consoleApplication
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param bool            $quietMode
     * @param array           $tasks
     */
    public function __construct(
        protected readonly SymfonyStyle    $console,
        protected readonly Application     $consoleApplication,
        protected readonly InputInterface  $input,
        protected readonly OutputInterface $output,
        protected bool                     $quietMode,
        protected array                    $tasks
    ) {
    }

    /**
     * Register.
     *
     * @param string $command
     * @param array  $arguments
     * @param array  $parameters
     * @return Task
     *
     * @throws InvalidArgumentException
     */
    public function command(string $command, array $arguments = [], array $parameters = []): Task {
        if ($this->consoleApplication->has($command)) {
            $this->tasks[] = $currentTask = new Task(
                $this->consoleApplication->find($command), $arguments, $parameters
            );

            return $currentTask;
        }

        throw new InvalidArgumentException(sprintf("Command with name '%s' does not exist", $command));
    }

    /**
     * Execute.
     *
     * @param \DateTime   $commandTime
     * @param LockFactory $lockFactory
     * @return int
     * @throws ExceptionInterface
     */
    public function execute(\DateTime $commandTime, LockFactory $lockFactory): int {
        $counter = 0;

        $this->block("Schedule Manager - Launch time %s", "fg=yellow", $commandTime->format("Y-m-d H:i:s"));

        if (count($this->tasks) <= 0) {
            $this->block("There are no tasks to run", "fg=yellow");
            return $counter;
        }

        /** @var Task $task */
        foreach ($this->tasks as $task) {
            $commandName = $task->getCommand()->getName();

            if ($task->isDue($commandTime)) {
                try {
                    if ($task->isWithoutOverlapping()) {
                        $lock = $lockFactory->createLock("symfony-schedule:" . $commandName);

                        if (!$lock->acquire()) {
                            $this->text("Skip '%s' command as it is already running", $commandName);
                            continue;
                        }
                    }

                    // Print information with task details
                    $this->block("Running '%s'", "fg=green", $commandName);
                    $this->text("  * Description: %s", $task->getDescription());
                    $this->text("  * Arguments: [%s]", implode(", ", $task->getArguments()));
                    $this->text("  * Parameters: [%s]", implode(", ", $task->getParameters()));

                    // Create command input as instance of ArrayInput
                    $commandInput = new ArrayInput(
                        array_merge(["command" => $commandName], $task->getArguments(), $task->getParameters())
                    );

                    // Execute task
                    $counter++;
                    $task->getCommand()->run($commandInput, $this->output);
                    $this->block("~~~", "fg=yellow");
                } catch (\Exception $exception) {
                    $this->block(
                        "Exception while running '%s' command with arguments [%s] and parameters [%s]: %s",
                        "fg=red",
                        $commandName,
                        implode(", ", $task->getArguments()),
                        implode(", ", $task->getParameters()),
                        $exception->getMessage()
                    );
                }
            } else {
                $this->text(
                    "Ignoring '%s' command with arguments [%s] and parameters [%s]. Next run at %s",
                    $commandName,
                    implode(", ", $task->getArguments()),
                    implode(", ", $task->getParameters()),
                    $task->nextDate($commandTime)->format("Y-m-d H:i")
                );
            }
        }

        return $counter;
    }

    /**
     * Check if Schedule is in quiet mode.
     *
     * @return bool
     */
    public function isQuietMode(): bool {
        return $this->quietMode;
    }

    /**
     * Enable or disable quiet mode.
     *
     * @param bool $quietMode
     */
    public function setQuietMode(bool $quietMode): void {
        $this->quietMode = $quietMode;
    }

    /**
     * Overwritten 'text' function to use 'sprintf' directly.
     *
     * @param string $message
     * @param mixed  ...$values
     * @return void
     */
    private function text(string $message, mixed ...$values): void {
        $this->console->text(sprintf($message, ...$values));
    }

    /**
     * Overwritten 'block' function to use 'sprintf' directly.
     *
     * @param string      $message
     * @param string|null $style
     * @param mixed       ...$values
     * @return void
     */
    private function block(string $message, ?string $style = null, mixed ...$values): void {
        $this->console->block(sprintf($message, ...$values), style: $style);
    }
}
