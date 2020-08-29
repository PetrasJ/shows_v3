<?php

namespace App\Controller;

use App\Service\EpisodeManager;
use App\Service\ShowManager;
use App\Service\UserEpisodeManager;
use App\Service\UserShowManager;
use App\Traits\LoggerTrait;
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
    use LoggerTrait;

    /**
     * @param UserShowManager $userShowService
     * @param EpisodeManager $episodeManager
     * @Route("/", name="index")
     * @return Response
     * @throws Exception
     */
    public function unwatched(UserShowManager $userShowService, EpisodeManager $episodeManager)
    {
        try {
            $shows = $userShowService->getShowsWithUnwatchedEpisodes();
            $episodes = $episodeManager->getEpisodes(
                new DateTime(),
                (new DateTime())->modify('+2 days'),
                true,
                true
            );
        } catch (NotFoundHttpException $e) {
            $this->error($e->getMessage(), [__METHOD__]);

            return new Response([], 500);
        }

        return $this->render('unwatched-shows/index.html.twig', ['shows' => $shows, 'episodes' => $episodes]);
    }

    /**
     * @param int $userShowId
     * @param UserEpisodeManager $userEpisodeService
     * @param ShowManager $showManager
     * @Route("/episodes/{userShowId}", name="episodes")
     * @return Response
     */
    public function unwatchedEpisodes(
        int $userShowId,
        UserEpisodeManager $userEpisodeService,
        ShowManager $showManager
    ) {
        try {
            $episodes = $userEpisodeService->getUnwatchedEpisodes($userShowId);
            $show = $showManager->getShow($userShowId);
            $nextEpisode = $showManager->getNextEpisode($show['id']);
        } catch (NotFoundHttpException $e) {
            $this->error($e->getMessage(), [__METHOD__]);

            return new Response([], 500);
        }

        return $this->render('unwatched-shows/episodes.html.twig', [
            'episodes' => $episodes,
            'show' => $show,
            'nextEpisode' => $nextEpisode,
        ]);
    }

    /**
     * @param Request $request
     * @param UserEpisodeManager $userEpisodeService
     * @Route("/comment", name="comment")
     * @return JsonResponse
     */
    public function comment(Request $request, UserEpisodeManager $userEpisodeService)
    {
        try {
            $userEpisodeService->update($request->get('id'), $request->get('userShowId'),
                ['comment' => $request->get('comment')]);
        } catch (Exception $e) {
            $this->error($e->getMessage(), [__METHOD__]);

            return new JsonResponse(['success' => false], 500);
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * @param Request $request
     * @param UserEpisodeManager $userEpisodeService
     * @Route("/watch", name="watch")
     * @return JsonResponse
     */
    public function watch(Request $request, UserEpisodeManager $userEpisodeService)
    {
        try {
            $userEpisodeService->update($request->get('id'), $request->get('userShowId'), ['watch' => true]);
        } catch (Exception $e) {
            $this->error($e->getMessage(), [__METHOD__]);

            return new JsonResponse(['success' => false], 500);
        }

        return new JsonResponse(['success' => true]);
    }
}
