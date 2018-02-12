<?php

namespace Adamski\Symfony\ScheduleBundle\Command;

use Adamski\Symfony\ScheduleBundle\Model\ManagerInterface;
use Adamski\Symfony\ScheduleBundle\Model\Schedule;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScheduleCommand extends Command {

    /**
     * @var string
     */
    protected static $defaultName = "schedule:run";

    /**
     * @var ManagerInterface
     */
    protected $scheduleManager;

    /**
     * ScheduleCommand constructor.
     *
     * @param ManagerInterface $manager
     * @param null|string      $name
     */
    public function __construct(ManagerInterface $manager, ?string $name = null) {
        parent::__construct($name);
        $this->scheduleManager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this->setDescription("Runs scheduled tasks");
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        if ($scheduleManager = $this->getScheduleManager()) {
            $scheduleManager->schedule(
                $this->getSchedule()
            );
        }
    }

    /**
     * Get Schedule Manager.
     *
     * @return ManagerInterface|null
     */
    private function getScheduleManager() {
        if ($this->scheduleManager instanceof ManagerInterface) {
            return $this->scheduleManager;
        }

        return null;
    }

    /**
     * Get Schedule.
     *
     * @return Schedule
     */
    private function getSchedule() {
        return new Schedule(
            $this->getApplication()
        );
    }
}
