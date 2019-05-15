<?php

namespace Adamski\Symfony\ScheduleBundle\Model;

use Cron\CronExpression;
use DateTime;
use Symfony\Component\Console\Command\Command;

class Task {

    use ManagesFrequencies;

    /**
     * @var Command
     */
    protected $command;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var string
     */
    protected $cronExpression = "* * * * *";

    /**
     * @var string
     */
    protected $description;

    /**
     * @var boolean
     */
    protected $withoutOverlapping = false;

    /**
     * Task constructor.
     *
     * @param Command $command
     * @param array   $arguments
     * @param array   $parameters
     */
    public function __construct(Command $command, array $arguments = [], array $parameters = []) {
        $this->command = $command;
        $this->arguments = $arguments;
        $this->parameters = $parameters;
    }

    /**
     * @return Command
     */
    public function getCommand() {
        return $this->command;
    }

    /**
     * @return array
     */
    public function getArguments() {
        return $this->arguments;
    }

    /**
     * @return array
     */
    public function getParameters() {
        $parametersArray = [];

        foreach ($this->parameters as $parameterKey => $parameterValue) {
            if (substr($parameterKey, 0, 2) !== "--") {
                $parameterKey = sprintf("--%s", $parameterKey);
            }

            $parametersArray[$parameterKey] = $parameterValue;
        }

        return $parametersArray;
    }

    /**
     * @return string
     */
    public function getCronExpression() {
        return $this->cronExpression;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description) {
        $this->description = $description;
    }

    /**
     * Check if Task should be run.
     *
     * @param DateTime $commandTime
     * @return bool
     */
    public function isDue(DateTime $commandTime) {
        return CronExpression::factory($this->getCronExpression())->isDue($commandTime);
    }

    /**
     * Get next running date.
     *
     * @param DateTime $commandTime
     * @return DateTime
     */
    public function nextDate(DateTime $commandTime) {
        return CronExpression::factory($this->getCronExpression())->getNextRunDate($commandTime);
    }

    /**
     * Preventing Task Overlaps
     *
     * @return $this
     */
    public function withoutOverlapping() {
        $this->withoutOverlapping = true;

        return $this;
    }

    /**
     * Is task without overlapping
     *
     * @return bool
     */
    public function isWithoutOverlapping() {
        return $this->withoutOverlapping;
    }
}
