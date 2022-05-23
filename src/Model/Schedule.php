<?php

namespace Adamski\Symfony\ScheduleBundle\Model;

use InvalidArgumentException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Lock\LockFactory;

class Schedule {

    protected SymfonyStyle    $console;
    protected Application     $consoleApplication;
    protected InputInterface  $input;
    protected OutputInterface $output;
    protected bool            $quietMode;
    protected array           $tasks;

    /**
     * @param Application     $consoleApplication
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function __construct(Application $consoleApplication, InputInterface $input, OutputInterface $output) {
        $this->consoleApplication = $consoleApplication;
        $this->input = $input;
        $this->output = $output;
        $this->console = new SymfonyStyle($input, $output);
        $this->quietMode = false;
        $this->tasks = [];
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
     */
    public function execute(\DateTime $commandTime, LockFactory $lockFactory): int {
        $counter = 0;

        if (count($this->tasks) <= 0) {
            $this->console->info("There are no tasks to run");
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
                            $this->console->info(sprintf("Skip '%s' command as it is already running", $commandName));
                            continue;
                        }
                    }

                    // Print information with task details
                    $this->console->text(sprintf("Running '%s' command:", $commandName));
                    $this->console->listing([
                        sprintf("Description: %s", $task->getDescription()),
                        sprintf("Arguments: [%s]", implode(", ", $task->getArguments())),
                        sprintf("Parameters: [%s]", implode(", ", $task->getParameters()))
                    ]);

                    // Create command input as instance of ArrayInput
                    $commandInput = new ArrayInput(
                        array_merge(["command" => $commandName], $task->getArguments(), $task->getParameters())
                    );

                    // Execute task
                    $counter++;
                    $task->getCommand()->run($commandInput, $this->output);
                    if (!$this->isQuietMode()) $this->console->newLine();
                } catch (\Exception $exception) {
                    $this->console->error(
                        sprintf(
                            "Exception while running '%s' command with arguments [%s] and parameters [%s]: %s",
                            $commandName,
                            implode(", ", $task->getArguments()),
                            implode(", ", $task->getParameters()),
                            $exception->getMessage()
                        )
                    );
                }
            } else {
                $this->console->info(
                    sprintf(
                        "Ignoring '%s' command with arguments [%s] and parameters [%s]. Next run at %s",
                        $commandName,
                        implode(", ", $task->getArguments()),
                        implode(", ", $task->getParameters()),
                        $task->nextDate($commandTime)->format("Y-m-d H:i")
                    )
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
}
