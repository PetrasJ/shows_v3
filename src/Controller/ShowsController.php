<?php

namespace App\Controller;

use App\Service\ShowManager;
use App\Service\UserEpisodeManager;
use App\Service\UserShowManager;
use App\Traits\LoggerTrait;
use DateTime;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/shows", name="shows_")
 */
class ShowsController extends GeneralController
{
    use LoggerTrait;

    private UserShowManager $userShowService;
    private ShowManager $showManager;

    public function __construct(UserShowManager $userShowService, ShowManager $showManager)
    {
        $this->userShowService = $userShowService;
        $this->showManager = $showManager;
    }

    /**
     * @Route("/list/{status}", name="index", defaults={"status":"0"})
     */
    public function index(string $status): Response
    {
        $shows = $this->userShowService->getShows((int)$status);

        return $this->render('shows/index.html.twig', ['shows' => $shows, 'status' => $status]);
    }

    /**
     * @Route("/details/{userShowId}/{limit}", name="details", defaults={"limit"=100})
     */
    public function show(string $userShowId, string $limit): Response
    {
        $show = $this->userShowService->getUserShowAndEpisodes((int)$userShowId, (int)$limit);

        return $this->render('shows/show.html.twig', [
            'userShow' => $show['userShow'],
            'episodes' => $show['episodes'],
        ]);
    }

    /**
     * @Route("/actions/{userShowId}", name="actions")
     */
    public function actions(string $userShowId): Response
    {
        $show = $this->userShowService->getUserShow($userShowId);

        if ($show) {
            return $this->render(
                'shows/actions.html.twig',
                [
                    'show' => $show,
                    'status' => $show['userShowStatus'],
                    'unwatched' => $show['episodesCount'] - $show['watched'],
                ]
            );
        } else {
            return new Response('', Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @Route("/unwatch", name="watch")
     */
    public function unwatch(Request $request, UserEpisodeManager $userEpisodeService): JsonResponse
    {
        try {
            $userEpisodeService
                ->update($request->get('id'), $request->get('userShowId'), ['unwatch' => true]);
        } catch (Exception $e) {
            $this->error($e->getMessage(), [__METHOD__]);

            return $this->failJson();
        }

        return $this->successJson();
    }

    /**
     * @Route("/update", name="update")
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $this->userShowService
                ->updateShow($request->get('userShowId'), ['offset' => $request->get('value')]);
        } catch (NotFoundHttpException $e) {
            $this->error($e->getMessage(), [__METHOD__]);

            return $this->failJson();
        }

        return $this->successJson();
    }

    /**
     * @Route("/add/{showId}", name="add")
     */
    public function add(string $showId): JsonResponse
    {
        try {
            $this->userShowService->add($showId);
        } catch (Exception $e) {
            $this->error($e->getMessage(), [__METHOD__]);

            return $this->failJson();
        }

        return $this->successJson();
    }

    /**
     * @Route("/restore/{userShowId}", name="restore")
     */
    public function restore(string $userShowId): JsonResponse
    {
        try {
            $this->userShowService->update($userShowId, 'add');
        } catch (Exception $e) {
            $this->error($e->getMessage(), [__METHOD__]);

            return $this->failJson();
        }

        return $this->successJson();
    }

    /**
     * @Route("/archive/{userShowId}", name="archive")
     */
    public function archive(string $userShowId): JsonResponse
    {
        try {
            $this->userShowService->update($userShowId, 'archive');
        } catch (Exception $e) {
            $this->error($e->getMessage(), [__METHOD__]);

            return $this->failJson();
        }

        return $this->successJson();
    }

    /**
     * @Route("/watch-later/{userShowId}", name="watch_later")
     */
    public function watchLater(string $userShowId): JsonResponse
    {
        try {
            $this->userShowService->update($userShowId, 'watch-later');
        } catch (Exception $e) {
            $this->error($e->getMessage(), [__METHOD__]);

            return $this->failJson();
        }

        return $this->successJson();
    }

    /**
     * @Route("/remove/{userShowId}", name="remove")
     */
    public function remove(string $userShowId): Response
    {
        try {
            $this->userShowService->remove($userShowId);
        } catch (Exception $e) {
            $this->error($e->getMessage(), [__METHOD__]);

            return $this->failJson();
        }

        return $this->successJson();
    }

    /**
     * @Route("/watch-all/{userShowId}", name="watch_all")
     */
    public function watchAllEpisodes(string $userShowId): JsonResponse
    {
        try {
            $this->userShowService->watchAll($userShowId);
        } catch (Exception $e) {
            $this->error($e->getMessage(), [__METHOD__]);

            return $this->failJson();
        }

        return $this->successJson();
    }

    /**
     * @Route("/update-all", name="update_all")
     * @throws Exception
     * @IsGranted("ROLE_ADMIN")
     */
    public function updateShows(): Response
    {
        $start = (new DateTime())->format('Y-m-d H:i:s');
        [$updated, $newShows] = $this->showManager->update();
        $finish = (new DateTime())->format('Y-m-d H:i:s');

        return $this->render(
            'shows/update.html.twig',
            [
                'start' => $start,
                'updated' => $updated,
                'updatedList' => implode(', ', $updated),
                'added' => $newShows,
                'addedList' => implode(', ', $newShows),
                'finish' => $finish,
            ]
        );
    }
}
