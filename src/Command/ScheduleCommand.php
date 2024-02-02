<?php

namespace Adamski\Symfony\ScheduleBundle\Command;

use Adamski\Symfony\ScheduleBundle\Model\ManagerInterface;
use Adamski\Symfony\ScheduleBundle\Model\Schedule;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Lock\LockFactory;

#[AsCommand(name: "schedule:run", description: "Runs scheduled tasks")]
class ScheduleCommand extends Command {

    private \DateTime $commandTime;
    private LockFactory $lockFactory;
    private ManagerInterface $manager;

    /**
     * @param LockFactory      $lockFactory
     * @param ManagerInterface $manager
     */
    public function __construct(LockFactory $lockFactory, ManagerInterface $manager) {
        parent::__construct();
        $this->commandTime = new \DateTime();
        $this->lockFactory = $lockFactory;
        $this->manager = $manager;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $console = new SymfonyStyle($input, $output);
        $schedule = new Schedule($console, $this->getApplication(), $input, $output, false, []);

        // Generate tasks and execute all due
        $this->manager->schedule($schedule);
        $schedule->execute($this->commandTime, $this->lockFactory);

        return Command::SUCCESS;
    }
}
