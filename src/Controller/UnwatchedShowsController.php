<?php

namespace App\Controller;

use App\Service\UserEpisodeService;
use App\Service\UserShowService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/unwatched", name="unwatched_")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class UnwatchedShowsController extends AbstractController
{
    /**
     * @param UserShowService $userShowService
     * @Route("/", name="index")
     * @return Response
     */
    public function unwatched(UserShowService $userShowService)
    {
        $shows = $userShowService->getShowsWithUnwatchedEpisodes();

        return $this->render('unwatched-shows/index.html.twig', ['shows' => $shows]);
    }

    /**
     * @param int $showId
     * @param UserEpisodeService $userEpisodeService
     * @Route("/{showId}/episodes", name="episodes")
     * @return Response
     */
    public function unwatchedEpisodes(int $showId, UserEpisodeService $userEpisodeService)
    {
        $episodes = $userEpisodeService->getUnwatchedEpisodes($showId);

        return $this->render('unwatched-shows/episodes.html.twig', ['episodes' => $episodes]);
    }
}
