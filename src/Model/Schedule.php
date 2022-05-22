<?php

namespace Adamski\Symfony\ScheduleBundle\Model;

use DateTime;
use Exception;
use InvalidArgumentException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;

class Schedule {

    /**
     * @var Task[]
     */
    protected array $tasks;

    /**
     * @var Application
     */
    protected Application $consoleApplication;

    /**
     * @var OutputInterface
     */
    protected OutputInterface $consoleOutput;

    /**
     * @var boolean
     */
    protected bool $quietMode;

    /**
     * Schedule constructor.
     *
     * @param Application     $consoleApplication
     * @param OutputInterface $consoleOutput
     */
    public function __construct(Application $consoleApplication, OutputInterface $consoleOutput) {
        $this->consoleApplication = $consoleApplication;
        $this->consoleOutput = $consoleOutput;

        $this->tasks = [];
        $this->quietMode = false;
    }

    /**
     * Register Command to run.
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
     * Execute tasks.
     *
     * @param DateTime    $commandTime
     * @param LockFactory $lockFactory
     */
    public function execute(DateTime $commandTime, LockFactory $lockFactory): void {
        $this->writeEmptyLine();

        if (count($this->tasks) > 0) {
            foreach ($this->tasks as $task) {
                $commandName = $task->getCommand()->getName();

                if ($task->isDue($commandTime)) {
                    try {
                        if ($task->isWithoutOverlapping()) {
                            $lock = $lockFactory->createLock("symfony-schedule:" . $commandName);

                            if (!$lock->acquire()) {
                                $this->writeComment(
                                    sprintf("Skip '%s' command as it is already running", $commandName)
                                );

                                continue;
                            }
                        }

                        // Define Command Input variable
                        $commandInput = new ArrayInput(array_merge([
                            "command" => $task->getCommand()->getName()
                        ], $task->getArguments(), $task->getParameters()));

                        // Run Command
                        $this->writeInfo(sprintf("Running '%s' command:", $commandName));
                        $this->writeInfo(sprintf(" * Description: %s", $task->getDescription()));
                        $this->writeInfo(sprintf(" * Arguments: [%s]", implode(", ", $task->getArguments())));
                        $this->writeInfo(sprintf(" * Parameters: [%s]", implode(", ", $task->getParameters())), false, true);

                        $task->getCommand()->run($commandInput, $this->consoleOutput);
                        $this->writeEmptyLine();
                    } catch (Exception $exception) {
                        $this->writeError(
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
                    $this->writeComment(
                        sprintf(
                            "Ignoring '%s' command with arguments [%s] and parameters [%s]. Next running at %s",
                            $commandName,
                            implode(", ", $task->getArguments()),
                            implode(", ", $task->getParameters()),
                            $task->nextDate($commandTime)->format("Y-m-d H:i")
                        )
                    );
                }
            }
        } else {
            $this->writeComment("No defined tasks found");
        }
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
     * Write info message.
     *
     * @param string $content
     * @param bool   $lineBefore
     * @param bool   $lineAfter
     */
    private function writeInfo(string $content, bool $lineBefore = false, bool $lineAfter = false): void {
        $this->writeMessage($content, "info", $lineBefore, $lineAfter);
    }

    /**
     * Write error message.
     *
     * @param string $content
     * @param bool   $lineBefore
     * @param bool   $lineAfter
     */
    private function writeError(string $content, bool $lineBefore = false, bool $lineAfter = false): void {
        $this->writeMessage($content, "error", $lineBefore, $lineAfter);
    }

    /**
     * Write comment message.
     *
     * @param string $content
     * @param bool   $lineBefore
     * @param bool   $lineAfter
     */
    private function writeComment(string $content, bool $lineBefore = false, bool $lineAfter = false): void {
        $this->writeMessage($content, "comment", $lineBefore, $lineAfter);
    }

    /**
     * Write message with specified content and type.
     *
     * @param string $content
     * @param string $type
     * @param bool   $lineBefore
     * @param bool   $lineAfter
     */
    private function writeMessage(string $content, string $type = "info", bool $lineBefore = false, bool $lineAfter = false): void {
        if (!$this->isQuietMode()) {

            if ($lineBefore) {
                $this->writeEmptyLine();
            }

            $this->consoleOutput->writeln(
                sprintf("<%s>%s</%s>", $type, $content, $type)
            );

            if ($lineAfter) {
                $this->writeEmptyLine();
            }
        }
    }

    /**
     * Write empty lines.
     *
     * @param int $lines
     */
    private function writeEmptyLine(int $lines = 1): void {
        if (!$this->isQuietMode()) {
            for ($i = 0; $i < $lines; $i++) {
                $this->consoleOutput->writeln("");
            }
        }
    }
}
