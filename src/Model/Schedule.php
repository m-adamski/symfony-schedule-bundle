<?php

namespace Adamski\Symfony\ScheduleBundle\Model;

use DateTime;
use Exception;
use InvalidArgumentException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

class Schedule {

    /**
     * @var Task[]
     */
    protected $tasks;

    /**
     * @var Application
     */
    protected $consoleApplication;

    /**
     * @var OutputInterface
     */
    protected $consoleOutput;

    /**
     * Schedule constructor.
     *
     * @param Application     $consoleApplication
     * @param OutputInterface $consoleOutput
     */
    public function __construct(Application $consoleApplication, OutputInterface $consoleOutput) {
        $this->consoleApplication = $consoleApplication;
        $this->consoleOutput = $consoleOutput;
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
    public function command(string $command, array $arguments = [], array $parameters = []) {
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
     * @param DateTime $commandTime
     */
    public function execute(DateTime $commandTime) {
        foreach ($this->tasks as $task) {
            if ($task->isDue($commandTime)) {
                try {

                    // Define Command Input variable
                    $commandInput = new ArrayInput(array_merge([
                        "command" => $task->getCommand()->getName()
                    ], $task->getArguments(), $task->getParameters()));

                    // Run Command
                    $task->getCommand()->run($commandInput, $this->consoleOutput);
                } catch (Exception $exception) {
                    $this->consoleOutput->writeln(sprintf("<error>Exception while executing '%s' command</error>", $task->getCommand()->getName()));
                }
            }
        }
    }
}
