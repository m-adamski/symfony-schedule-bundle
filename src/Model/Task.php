<?php

namespace Adamski\Symfony\ScheduleBundle\Model;

use Cake\Chronos\Chronos;
use Cron\CronExpression;
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
        return $this->parameters;
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
     * @param Chronos $commandTime
     * @return bool
     */
    public function isDue(Chronos $commandTime) {
        return CronExpression::factory($this->getCronExpression())->isDue($commandTime);
    }
}
