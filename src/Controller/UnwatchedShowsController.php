<?php

namespace App\Controller;

use App\Service\UserEpisodeService;
use App\Service\UserShowService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
        try {
            $shows = $userShowService->getShowsWithUnwatchedEpisodes();
        } catch (NotFoundHttpException $e) {
            return new Response([], 404);
        }

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
        try {
            $episodes = $userEpisodeService->getUnwatchedEpisodes($showId);
        } catch (NotFoundHttpException $e) {
            return new Response([], 404);
        }

        return $this->render('unwatched-shows/episodes.html.twig', ['episodes' => $episodes]);
    }

    /**
     * @param Request $request
     * @param UserEpisodeService $userEpisodeService
     * @Route("/comment", name="comment")
     * @return JsonResponse
     */
    public function comment(Request $request, UserEpisodeService $userEpisodeService)
    {
        $userEpisodeService->comment($request->get('id'), $request->get('comment'));

        return new JsonResponse(['success' => true]);
    }
}
