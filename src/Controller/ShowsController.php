<?php

namespace App\Controller;

use App\Service\UserShowsService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ShowsController
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class ShowsController extends AbstractController
{
    /**
     * @param UserShowsService $userShowsService
     * @Route("/unwatched", name="unwatched")
     * @return Response
     */
    public function unwatched(UserShowsService $userShowsService)
    {
        $shows = $userShowsService->getShowsWithUnwatchedEpisodes();
        return $this->render('shows/index.html.twig', ['shows' => $shows]);
    }
}
