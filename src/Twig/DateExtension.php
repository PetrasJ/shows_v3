<?php

namespace App\Twig;

use App\Traits\LoggerTrait;
use DateTime;
use DateTimeZone;
use Exception;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DateExtension extends AbstractExtension
{
    use LoggerTrait;

    private string $timezone;

    public function __construct(Security $security)
    {
        $user = $security->getUser();
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
        if (!$date instanceof DateTime) {
            try {
                $date = DateTime::createFromFormat('Y-m-d H:i:s', $date);
            } catch (Exception $e) {
                $this->error($e->getMessage(), [__METHOD__]);

                return '';
            }
        }

        return $date instanceof DateTime
            ? $date->setTimeZone($this->timezone)->format('Y-m-d H:i')
            : '';
    }
}
