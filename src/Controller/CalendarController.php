<?php

namespace App\Controller;

use App\Service\EpisodesManager;
use DateInterval;
use DatePeriod;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/calendar", name="calendar_")
 */
class CalendarController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(Security $security): Response
    {
        return $this
            ->render('calendar/index.html.twig',
                [
                    'currentMonth' => date('Y-m'),
                    'include' => $security->getUser() ? $security->getUser()->getCalendarShow() : [],
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

        return [
          'from' => $from,
          'to' => $to,
          'days' => $days
        ];
    }
}
