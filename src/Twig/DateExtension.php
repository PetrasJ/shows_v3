<?php

namespace App\Twig;

use App\Service\Storage;
use DateTime;
use DateTimeZone;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DateExtension extends AbstractExtension
{
    private $timezone;

    public function __construct(Storage $storage)
    {
        $this->timezone = $storage->getUser()->getTimezone()
            ? new DateTimeZone($storage->getUser()->getTimezone())
            : new DateTimeZone('Europe/Vilnius');
    }

    public function getFilters()
    {
        return [
            new TwigFilter('dateTimezone', [$this, 'dateTimezone']),
        ];
    }

    public function dateTimezone(DateTime $date)
    {
        return $date->setTimeZone($this->timezone)->format('Y-m-d H:i');
    }
}
