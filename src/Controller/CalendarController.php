<?php

namespace App\Controller;

use App\Service\EpisodesManager;
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
     * @Route("/", name="index")
     * @return Response
     */
    public function index()
    {
        return $this->render('calendar/index.html.twig', ['currentMonth' => date('Y-m')]);
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
        $from = DateTime::createFromFormat('Y-m', $month)->modify('first day of this month 00:00:00');
        $to = DateTime::createFromFormat('Y-m', $month)->modify('last day of this month 23:59:59');
        $episodes = $episodesManager->getEpisodes($from, $to);

        $now = new DateTime();
        $tz = date_default_timezone_get();
        return $this->render('calendar/month.html.twig', ['month' => $month, 'episodes' => $episodes, 'now' => $now, 'tz' => $tz]);
    }

}
