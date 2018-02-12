<?php

namespace Adamski\Symfony\ScheduleBundle\Model;

use Symfony\Component\Console\Application;

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
     * Schedule constructor.
     *
     * @param Application $consoleApplication
     */
    public function __construct(Application $consoleApplication) {
        $this->consoleApplication = $consoleApplication;
    }

    public function command(string $command, array $arguments = [], array $parameters = []) {

    }
}
