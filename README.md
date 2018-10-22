# Schedule Bundle for Symfony 4

Bundle for simplifying operations with CRON jobs.

## Installation

This Bundle can be installed by Composer:

```
$ composer require m-adamski/symfony-schedule-bundle
```

## Configuration

To use this bundle, you need to register it in ``config/bundles.php`` - Symfony Flex should do it automatically.

```(php)
return [
    Adamski\Symfony\ScheduleBundle\ScheduleBundle::class => ['all' => true],
];
```

## How to use it?

Bundle provide functionality to manage CRON jobs by configuration from specified file.
We need to create class implementing ``Adamski\Symfony\ScheduleBundle\Model\ManagerInterface`` interface.

```(php)
namespace App\Model;

use Adamski\Symfony\ScheduleBundle\Model\ManagerInterface;
use Adamski\Symfony\ScheduleBundle\Model\Schedule;

class ScheduleManager implements ManagerInterface {

    public function schedule(Schedule $schedule) {
        // TODO: ..
    }
}
```

Then we need to complete the configuration - create file ``config/packages/schedule.yaml`` and set path to ScheduleManager:

```(yaml)
schedule:
    manager: App\Model\ScheduleManager
```

## Command schedule configuration

In function ``schedule`` we can configure CRON jobs with required expressions.
For example we want run ``app:test-command`` daily at 12:00:

```(php)
public function schedule(Schedule $schedule) {
    $schedule->command("app:test-command")->dailyAt("12:00");
}
```

Schedule class provide many date-time manipulators. This functionality is inspired by Laravel Tasks Scheduling.

## Schedule Frequency Options

There are many schedules that you can assign to your task.

| Method                                         |
| ---------------------------------------------- |
| cron($expression)                              |
| everyMinute()                                  |
| everyFiveMinutes()                             |
| cron($expression)                              |
| everyMinute()                                  |
| everyFiveMinutes()                             |
| everyTenMinutes()                              |
| everyFifteenMinutes()                          |
| everyThirtyMinutes()                           |
| hourly()                                       |
| hourlyAt(int $offset)                          |
| daily()                                        |
| at(string $time)                               |
| dailyAt(string $time)                          |
| twiceDaily(int $first = 1, int $second = 13)   |
| weekdays()                                     |
| weekends()                                     |
| mondays()                                      |
| tuesdays()                                     |
| wednesdays()                                   |
| thursdays()                                    |
| fridays()                                      |
| saturdays()                                    |
| sundays()                                      |
| weekly()                                       |
| weeklyOn(int $day, string $time = "0:0")       |
| monthly()                                      |
| monthlyOn(int $day = 1, string $time = "0:0")  |
| twiceMonthly(int $first = 1, int $second = 16) |
| quarterly()                                    |
| yearly()                                       |
| days($days)                                    |

## CRON

Now it's enough to insert only one entry into crontab on server:

```
* * * * * php /path-to-project/bin/console schedule:run >> schedule.log 2>&1
```

## License

MIT
