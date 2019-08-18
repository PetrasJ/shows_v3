<?php

namespace App\Controller;

use App\Service\EpisodesManager;
use App\Service\Storage;
use DateInterval;
use DatePeriod;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/calendar", name="calendar_")
 */
class CalendarController extends AbstractController
{
    /**
     * @param Storage $storage
     * @Route("/", name="index")
     * @return Response
     */
    public function index(Storage $storage)
    {
        return $this
            ->render('calendar/index.html.twig',
                [
                    'currentMonth' => date('Y-m'),
                    'include' => $storage->getUser() ? $storage->getUser()->getCalendarShow() : [],
                ]
            );
    }

    /**
     * @param string $month
     * @param EpisodesManager $episodesManager
     * @Route("/month/{month}", name="calendar")
     * @return Response
     * @throws Exception
     */
    public function month($month, EpisodesManager $episodesManager)
    {
        $period = $this->getPeriod($month);
        $episodes = $episodesManager->getEpisodes($period['from'], $period['to']);

        return $this->render('calendar/month.html.twig', [
            'month' => $month,
            'days' => $period['days'],
            'episodes' => $episodes,
        ]);
    }

    private function getPeriod(string $month): array
    {
        $date = explode('-', $month);
        $from = (new DateTime)
            ->setDate($date[0], $date[1], 15)
            ->modify('first day of this month')
        ;

        $from = $from->format('D') === 'Mon'
            ? $from->modify('+12 hour')
            : $from->modify('previous monday')->modify('+12 hour');

        $to = (new DateTime)
            ->setDate($date[0], $date[1], 15)
            ->modify('last day of this month')
        ;

        $to = $to->format('D') === 'Sun'
            ? $to->modify('+20 hour')
            : $to->modify('next sunday')->modify('+20 hour');

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

        return [
          'from' => $from,
          'to' => $to,
          'days' => $days
        ];
    }
}
