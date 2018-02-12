<?php

namespace Adamski\Symfony\ScheduleBundle\Model;

interface ManagerInterface {

    public function schedule(Schedule $schedule);
}
