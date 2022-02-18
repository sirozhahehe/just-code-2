<?php

namespace App\Utils;

use DateInterval;
use DatePeriod;
use DateTime;

class DateRange
{
    public function __construct(
        private DateTime $start,
        private DateTime $end,
    ) {}

    public function getStartDate(): DateTime
    {
        return $this->start;
    }

    public function getEndDate(): DateTime
    {
        return $this->end;
    }

    public function getDatePeriod(string $interval): DatePeriod
    {
        return new DatePeriod($this->start, new DateInterval($interval), $this->end);
    }

    public function modifyStartImmutable(string $modifier): DateTime
    {
        return (clone $this->start)->modify($modifier);
    }

    public function modifyEndImmutable(string $modifier): DateTime
    {
        return (clone $this->end)->modify($modifier);
    }
}