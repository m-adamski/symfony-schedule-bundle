<?php

namespace Adamski\Symfony\ScheduleBundle\Command;

use Adamski\Symfony\ScheduleBundle\DependencyInjection\ScheduleExtension;
use Adamski\Symfony\ScheduleBundle\Model\ManagerInterface;
use Adamski\Symfony\ScheduleBundle\Model\Schedule;
use DateTime;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\Factory as LockFactory;

class ScheduleCommand extends Command {

    /**
     * @var string
     */
    protected static $defaultName = "schedule:run";

    /**
     * @var DateTime
     */
    protected $commandTime;

    /**
     * @var ManagerInterface
     */
    protected $scheduleManager;

    /**
     * @var LockFactory
     */
    protected $lockFactory;

    /**
     * ScheduleCommand constructor.
     *
     * @param ManagerInterface $manager
     * @param null|string      $name
     */
    public function __construct(ManagerInterface $manager, LockFactory $lockFactory, ?string $name = null) {
        parent::__construct($name);
        $this->commandTime = new DateTime();
        $this->scheduleManager = $manager;
        $this->lockFactory = $lockFactory;
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
            $schedule = $this->getSchedule($output);

            // Generate tasks collection and then execute all due tasks
            $scheduleManager->schedule($schedule);
            $schedule->execute($this->commandTime, $this->lockFactory);
        } else {
            throw new InvalidArgumentException(sprintf("It looks like there is already registered service under the '%s' name", ScheduleExtension::$serviceName));
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
     * @param OutputInterface $output
     * @return Schedule
     */
    private function getSchedule(OutputInterface $output) {
        return new Schedule(
            $this->getApplication(), $output
        );
    }
}
