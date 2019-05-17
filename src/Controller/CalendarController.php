<?php

namespace App\Controller;

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
     * @param string $month
     * @Route("/month/{month}", name="calendar")
     * @return Response
     */
    public function month($month)
    {
        return $this->render('calendar/month.html.twig', ['month' => $month]);
    }

}
