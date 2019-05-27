<?php

namespace App\Controller;

use App\Service\EpisodesManager;
use App\Service\Storage;
use DateTime;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/calendar", name="calendar_")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY', 'IS_AUTHENTICATED_REMEMBERED')")
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
        $from = DateTime::createFromFormat('Y-m', $month)->modify('first day of this month 00:00:00');
        $to = DateTime::createFromFormat('Y-m', $month)->modify('last day of this month 23:59:59');
        $episodes = $episodesManager->getEpisodes($from, $to);

        return $this->render('calendar/month.html.twig', [
            'month' => $month,
            'episodes' => $episodes,
        ]);
    }
}
