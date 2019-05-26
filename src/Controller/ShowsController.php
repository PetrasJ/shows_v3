<?php

namespace App\Controller;

use App\Service\UserEpisodeService;
use App\Service\UserShowService;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/shows", name="shows_")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class ShowsController extends AbstractController
{
    private $userShowService;

    public function __construct(UserShowService $userShowService)
    {
        $this->userShowService = $userShowService;
    }

    /**
     * @param                 $status
     * @Route("/list/{status}", name="index", defaults={"status":"0"})
     * @return Response
     */
    public function index($status)
    {
        $shows = $this->userShowService->getShows($status);

        return $this->render('shows/index.html.twig', ['shows' => $shows, 'status' => $status]);
    }

    /**
     * @param $userShowId
     * @param int $limit
     * @Route("/details/{userShowId}/{limit}", name="details", defaults={"limit"=100})
     * @return Response
     */
    public function show($userShowId, $limit)
    {
        $show = $this->userShowService->getUserShowAndEpisodes($userShowId, $limit);

        return $this->render('shows/show.html.twig', [
            'userShow' => $show['userShow'],
            'episodes' => $show['episodes'],
            ]);
    }

    /**
     * @param Request $request
     * @param UserEpisodeService $userEpisodeService
     * @Route("/unwatch", name="watch")
     * @return JsonResponse
     */
    public function unwatch(Request $request, UserEpisodeService $userEpisodeService)
    {
        try {
            $userEpisodeService->update($request->get('id'), $request->get('userShowId'), ['unwatch' => true]);
        } catch (Exception $e) {
            return new JsonResponse(['success' => false], 404);
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * @param Request $request
     * @Route("/update", name="update")
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        try {
            $this->userShowService->updateShow($request->get('userShowId'), ['offset' => $request->get('value')]);
        } catch (NotFoundHttpException $e) {
            return new JsonResponse(['success' => false], 404);
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * @param string $showId
     * @Route("/add/{showId}", name="add")
     * @return JsonResponse
     */
    public function add(string $showId)
    {
        try {
            $this->userShowService->add($showId);
        } catch (Exception $e) {
            return new JsonResponse(['success' => false], 404);
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * @param string $userShowId
     * @Route("/archive/{userShowId}", name="archive")
     * @return JsonResponse
     */
    public function archive(string $userShowId)
    {
        try {
            $this->userShowService->update($userShowId, 'archive');
        } catch (Exception $e) {
            return new JsonResponse(['success' => false], 404);
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * @param string $userShowId
     * @Route("/watch-later/{userShowId}", name="watch_later")
     * @return JsonResponse
     */
    public function watchLater(string $userShowId)
    {
        try {
            $this->userShowService->update($userShowId, 'watch-later');
        } catch (Exception $e) {
            return new JsonResponse(['success' => false], 404);
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * @param string $userShowId
     * @Route("/remove/{userShowId}", name="remove")
     * @return Response
     */
    public function remove(string $userShowId)
    {
        try {
            $this->userShowService->remove($userShowId);
        } catch (Exception $e) {
            return new JsonResponse(['success' => false], 404);
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * @param string $userShowId
     * @Route("/watch-all/{userShowId}", name="watch_all")
     * @return Response
     */
    public function watchAllEpisodes(string $userShowId)
    {
        try {
            $this->userShowService->watchAll($userShowId);
        } catch (Exception $e) {
            return new JsonResponse(['success' => false], 404);
        }

        return new JsonResponse(['success' => true]);
    }
}
