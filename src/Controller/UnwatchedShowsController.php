<?php

namespace App\Controller;

use App\Service\EpisodesManager;
use App\Service\ShowsManager;
use App\Service\UserEpisodeService;
use App\Service\UserShowService;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="unwatched_")
 */
class UnwatchedShowsController extends AbstractController
{
    /**
     * @param UserShowService $userShowService
     * @param EpisodesManager $episodesManager
     * @Route("/", name="index")
     * @return Response
     * @throws Exception
     */
    public function unwatched(UserShowService $userShowService, EpisodesManager $episodesManager)
    {
        try {
            $shows = $userShowService->getShowsWithUnwatchedEpisodes();
        } catch (NotFoundHttpException $e) {
            return new Response([], 404);
        }

        $from = new DateTime();
        $to = (new DateTime())->modify('+2 days');
        $episodes = $episodesManager->getEpisodes($from, $to);

        return $this->render('unwatched-shows/index.html.twig', ['shows' => $shows, 'episodes' => $episodes]);
    }

    /**
     * @param int                $userShowId
     * @param UserEpisodeService $userEpisodeService
     * @param ShowsManager       $showsManager
     * @Route("/episodes/{userShowId}", name="episodes")
     * @return Response
     */
    public function unwatchedEpisodes(int $userShowId, UserEpisodeService $userEpisodeService, ShowsManager $showsManager)
    {
        try {
            $episodes = $userEpisodeService->getUnwatchedEpisodes($userShowId);
            $show = $showsManager->getShow($userShowId);
        } catch (NotFoundHttpException $e) {
            return new Response([], 404);
        }

        return $this->render('unwatched-shows/episodes.html.twig', ['episodes' => $episodes, 'show' => $show]);
    }

    /**
     * @param Request            $request
     * @param UserEpisodeService $userEpisodeService
     * @Route("/comment", name="comment")
     * @return JsonResponse
     */
    public function comment(Request $request, UserEpisodeService $userEpisodeService)
    {
        try {
            $userEpisodeService->update($request->get('id'), $request->get('userShowId'), ['comment' => $request->get('comment')]);
        } catch (Exception $e) {
            return new JsonResponse(['success' => false], 404);
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * @param Request            $request
     * @param UserEpisodeService $userEpisodeService
     * @Route("/watch", name="watch")
     * @return JsonResponse
     */
    public function watch(Request $request, UserEpisodeService $userEpisodeService)
    {
        try {
            $userEpisodeService->update($request->get('id'), $request->get('userShowId'), ['watch' => true]);
        } catch (Exception $e) {
            return new JsonResponse(['success' => false], 404);
        }

        return new JsonResponse(['success' => true]);
    }
}
