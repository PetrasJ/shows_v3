<?php

namespace App\Controller;

use App\Service\EpisodeManager;
use App\Service\PeriodManager;
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
     * @Route("/month/{month}", name="calendar")
     * @throws Exception
     */
    public function month($month, EpisodeManager $episodeManager, PeriodManager $periodManager): Response
    {
        $period = $periodManager->getPeriod($month);
        $episodes = $episodeManager->getEpisodes($period['from'], $period['to']);

        return $this->render('calendar/month.html.twig', [
            'month' => $month,
            'days' => $period['days'],
            'episodes' => $episodes,
        ]);
    }
}
