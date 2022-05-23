<?php

namespace Adamski\Symfony\ScheduleBundle\Model;

trait ManagesFrequencies {

    /**
     * The Cron expression representing the event's frequency.
     *
     * @param string $expression
     * @return $this
     */
    public function cron(string $expression): static {
        $this->cronExpression = $expression;

        return $this;
    }

    /**
     * Schedule the event to run every minute.
     *
     * @return $this
     */
    public function everyMinute(): static {
        return $this->spliceIntoPosition(1, "*");
    }

    /**
     * Schedule the event to run every five minutes.
     *
     * @return $this
     */
    public function everyFiveMinutes(): static {
        return $this->spliceIntoPosition(1, "*/5");
    }

    /**
     * Schedule the event to run every ten minutes.
     *
     * @return $this
     */
    public function everyTenMinutes(): static {
        return $this->spliceIntoPosition(1, "*/10");
    }

    /**
     * Schedule the event to run every fifteen minutes.
     *
     * @return $this
     */
    public function everyFifteenMinutes(): static {
        return $this->spliceIntoPosition(1, "*/15");
    }

    /**
     * Schedule the event to run every thirty minutes.
     *
     * @return $this
     */
    public function everyThirtyMinutes(): static {
        return $this->spliceIntoPosition(1, "0,30");
    }

    /**
     * Schedule the event to run hourly.
     *
     * @return $this
     */
    public function hourly(): static {
        return $this->spliceIntoPosition(1, 0);
    }

    /**
     * Schedule the event to run hourly at a given offset in the hour.
     *
     * @param int $offset
     * @return $this
     */
    public function hourlyAt(int $offset): static {
        return $this->spliceIntoPosition(1, $offset);
    }

    /**
     * Schedule the event to run daily.
     *
     * @return $this
     */
    public function daily(): static {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0);
    }

    /**
     * Schedule the command at a given time.
     *
     * @param string $time
     * @return $this
     */
    public function at(string $time): static {
        return $this->dailyAt($time);
    }

    /**
     * Schedule the event to run daily at a given time (10:00, 19:30, etc).
     *
     * @param string $time
     * @return $this
     */
    public function dailyAt(string $time): static {
        $segments = explode(":", $time);

        return $this->spliceIntoPosition(2, (int)$segments[0])
            ->spliceIntoPosition(1, count($segments) == 2 ? (int)$segments[1] : "0");
    }

    /**
     * Schedule the event to run twice daily.
     *
     * @param int $first
     * @param int $second
     * @return $this
     */
    public function twiceDaily(int $first = 1, int $second = 13): static {
        $hours = $first . "," . $second;

        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, $hours);
    }

    /**
     * Schedule the event to run only on weekdays.
     *
     * @return $this
     */
    public function weekdays(): static {
        return $this->spliceIntoPosition(5, "1-5");
    }

    /**
     * Schedule the event to run only on weekends.
     *
     * @return $this
     */
    public function weekends(): static {
        return $this->spliceIntoPosition(5, "0,6");
    }

    /**
     * Schedule the event to run only on Mondays.
     *
     * @return $this
     */
    public function mondays(): static {
        return $this->days(1);
    }

    /**
     * Schedule the event to run only on Tuesdays.
     *
     * @return $this
     */
    public function tuesdays(): static {
        return $this->days(2);
    }

    /**
     * Schedule the event to run only on Wednesdays.
     *
     * @return $this
     */
    public function wednesdays(): static {
        return $this->days(3);
    }

    /**
     * Schedule the event to run only on Thursdays.
     *
     * @return $this
     */
    public function thursdays(): static {
        return $this->days(4);
    }

    /**
     * Schedule the event to run only on Fridays.
     *
     * @return $this
     */
    public function fridays(): static {
        return $this->days(5);
    }

    /**
     * Schedule the event to run only on Saturdays.
     *
     * @return $this
     */
    public function saturdays(): static {
        return $this->days(6);
    }

    /**
     * Schedule the event to run only on Sundays.
     *
     * @return $this
     */
    public function sundays(): static {
        return $this->days(0);
    }

    /**
     * Schedule the event to run weekly.
     *
     * @return $this
     */
    public function weekly(): static {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(5, 0);
    }

    /**
     * Schedule the event to run weekly on a given day and time.
     *
     * @param int    $day
     * @param string $time
     * @return $this
     */
    public function weeklyOn(int $day, string $time = "0:0"): static {
        $this->dailyAt($time);

        return $this->spliceIntoPosition(5, $day);
    }

    /**
     * Schedule the event to run monthly.
     *
     * @return $this
     */
    public function monthly(): static {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(3, 1);
    }

    /**
     * Schedule the event to run monthly on a given day and time.
     *
     * @param int    $day
     * @param string $time
     * @return $this
     */
    public function monthlyOn(int $day = 1, string $time = "0:0"): static {
        $this->dailyAt($time);

        return $this->spliceIntoPosition(3, $day);
    }

    /**
     * Schedule the event to run twice monthly.
     *
     * @param int $first
     * @param int $second
     * @return $this
     */
    public function twiceMonthly(int $first = 1, int $second = 16): static {
        $days = $first . "," . $second;

        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(3, $days);
    }

    /**
     * Schedule the event to run quarterly.
     *
     * @return $this
     */
    public function quarterly(): static {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(3, 1)
            ->spliceIntoPosition(4, "1-12/3");
    }

    /**
     * Schedule the event to run yearly.
     *
     * @return $this
     */
    public function yearly(): static {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(3, 1)
            ->spliceIntoPosition(4, 1);
    }

    /**
     * Set the days of the week the command should run on.
     *
     * @param array|mixed $days
     * @return $this
     */
    public function days(mixed $days): static {
        $days = is_array($days) ? $days : func_get_args();

        return $this->spliceIntoPosition(5, implode(",", $days));
    }

    /**
     * Splice the given value into the given position of the expression.
     *
     * @param int    $position
     * @param string $value
     * @return $this
     */
    protected function spliceIntoPosition(int $position, string $value): static {
        $segments = explode(" ", $this->cronExpression);
        $segments[$position - 1] = $value;

        return $this->cron(implode(" ", $segments));
    }
}
