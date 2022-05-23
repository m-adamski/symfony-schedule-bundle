<?php

namespace Adamski\Symfony\ScheduleBundle\Model;

use Cron\CronExpression;
use Symfony\Component\Console\Command\Command;

class Task {

    use ManagesFrequencies;

    protected Command $command;
    protected array   $arguments;
    protected array   $parameters;
    protected string  $cronExpression     = "* * * * *";
    protected string  $description        = "-";
    protected bool    $withoutOverlapping = false;

    /**
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
    public function getCommand(): Command {
        return $this->command;
    }

    /**
     * @return array
     */
    public function getArguments(): array {
        return $this->arguments;
    }

    /**
     * @return array
     */
    public function getParameters(): array {
        $parametersArray = [];

        foreach ($this->parameters as $parameterKey => $parameterValue) {
            if (!str_starts_with($parameterKey, "--")) {
                $parameterKey = sprintf("--%s", $parameterKey);
            }

            $parametersArray[$parameterKey] = $parameterValue;
        }

        return $parametersArray;
    }

    /**
     * @return string
     */
    public function getCronExpression(): string {
        return $this->cronExpression;
    }

    /**
     * @return string
     */
    public function getDescription(): string {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void {
        $this->description = $description;
    }

    /**
     * Check if Task should be run.
     *
     * @param \DateTime $commandTime
     * @return bool
     */
    public function isDue(\DateTime $commandTime): bool {
        $cronExpression = new CronExpression($this->getCronExpression());
        return $cronExpression->isDue($commandTime);
    }

    /**
     * Get next running date.
     *
     * @param \DateTime $commandTime
     * @return \DateTime|null
     */
    public function nextDate(\DateTime $commandTime): ?\DateTime {
        try {
            $cronExpression = new CronExpression($this->getCronExpression());
            return $cronExpression->getNextRunDate($commandTime);
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * Preventing Task Overlaps
     *
     * @return $this
     */
    public function withoutOverlapping(): static {
        $this->withoutOverlapping = true;

        return $this;
    }

    /**
     * Is task without overlapping
     *
     * @return bool
     */
    public function isWithoutOverlapping(): bool {
        return $this->withoutOverlapping;
    }
}
