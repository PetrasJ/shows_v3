<?php

namespace App\Twig;

use App\Service\Storage;
use DateTime;
use DateTimeZone;
use Exception;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DateExtension extends AbstractExtension
{
    private $timezone;

    public function __construct(Storage $storage)
    {
        $user = $storage->getUser();
        if ($user && $user->getTimezone()) {
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

    public function dateTimezone($date)
    {
        if (!$date instanceof DateTime){
            try {
                $date = DateTime::createFromFormat('Y-m-d H:i:s', $date);
            } catch (Exception $e) {
                return '';
            }
        }
        return $date instanceof DateTime
            ? $date->setTimeZone($this->timezone)->format('Y-m-d H:i')
            : '';
    }
}
