<?php

namespace App\Service;

use DateInterval;
use DatePeriod;
use DateTime;

class PeriodManager
{
    public function getPeriod(string $month): array
    {
        $date = explode('-', $month);
        $from = (new DateTime)
            ->setDate($date[0], $date[1], 15)
            ->modify('first day of this month')
        ;

        $from = $from->format('D') === 'Mon'
            ? $from->setTime(0, 0)
            : $from->modify('previous monday')->setTime(0, 0);

        $to = (new DateTime)
            ->setDate($date[0], $date[1], 15)
            ->modify('last day of this month')
        ;

        $to = $to->format('D') === 'Sun'
            ? $to->setTime(23, 59)
            : $to->modify('next sunday')->setTime(23, 59);

        $period = new DatePeriod(
            $from,
            new DateInterval('P1D'),
            $to
        );

        $days = [];
        foreach ($period as $key => $value) {
            /** @var DateTime $value */
            $days[] = $value->format('Y-m-d');
        }

        return [$from, $to, $days];
    }
}
