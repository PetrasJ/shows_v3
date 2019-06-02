<?php

namespace App\Controller;

use App\Service\EpisodesManager;
use App\Service\Storage;
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
                ['currentMonth' => date('Y-m'), 'include' => $storage->getUser()->getCalendarShow()]
            );
    }

    /**
     * @param string          $month
     * @param EpisodesManager $episodesManager
     * @Route("/month/{month}", name="calendar")
     * @return Response
     * @throws Exception
     */
    public function month($month, EpisodesManager $episodesManager)
    {
        $date = explode('-', $month);
        $from = (new DateTime)->setDate($date[0], $date[1], 15)->modify('first day of this month 00:00:00');
        $to = (new DateTime)->setDate($date[0], $date[1], 15)->modify('last day of this month 23:59:59');
        $episodes = $episodesManager->getEpisodes($from, $to);

        return $this->render('calendar/month.html.twig', [
            'month' => $month,
            'episodes' => $episodes,
        ]);
    }
}
