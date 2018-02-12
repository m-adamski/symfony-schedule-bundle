<?php

namespace Adamski\Symfony\ScheduleBundle\Command;

use Adamski\Symfony\ScheduleBundle\Model\ManagerInterface;
use Adamski\Symfony\ScheduleBundle\Model\Schedule;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ScheduleCommand extends Command {

    /**
     * @var string
     */
    protected static $defaultName = "schedule:run";

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * ScheduleCommand constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        parent::__construct();
        $this->container = $container;
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
        if ($scheduleManager = $this->getContainer()->getParameter("schedule.manager")) {
            if ($scheduleManager instanceof ManagerInterface) {
                return $scheduleManager;
            }
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

    /**
     * Get Container.
     *
     * @return ContainerInterface
     */
    private function getContainer() {
        return $this->container;
    }
}
