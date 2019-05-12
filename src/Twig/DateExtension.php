<?php

namespace App\Twig;

use App\Entity\User;
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
        $user = $storage->getUser();
        if ($user instanceof User && $user->getTimezone()) {
            $this->timezone = new DateTimeZone($user->getTimezone());
        } else {
            $this->timezone = new DateTimeZone('UTC');
        }
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
