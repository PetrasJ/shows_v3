<?php

namespace App\Controller;

use App\Entity\UserEpisode;
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
     * @param $showId
     * @param int $limit
     * @Route("/details/{showId}/{limit}", name="details", defaults={"limit"=100})
     * @return Response
     */
    public function show($showId, $limit)
    {
        $show = $this->userShowService->getUserShowAndEpisodes($showId, $limit);

        return $this->render('shows/show.html.twig', [
            'show' => $show['show'],
            'episodes' => $show['episodes'],
            ]);
    }

    /**
     * @param Request $request
     * @Route("/update", name="update")
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        try {
            $this->userShowService->updateShow($request->get('id'), ['offset' => $request->get('value')]);
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
            $this->userShowService->update($showId, 'add');
        } catch (Exception $e) {
            return new JsonResponse(['success' => false], 404);
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * @param string $showId
     * @Route("/archive/{showId}", name="archive")
     * @return JsonResponse
     */
    public function archive(string $showId)
    {
        try {
            $this->userShowService->update($showId, 'archive');
        } catch (Exception $e) {
            return new JsonResponse(['success' => false], 404);
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * @param string $showId
     * @Route("/watch-later/{showId}", name="watch_later")
     * @return JsonResponse
     */
    public function watchLater(string $showId)
    {
        try {
            $this->userShowService->update($showId, 'watch-later');
        } catch (Exception $e) {
            return new JsonResponse(['success' => false], 404);
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * @param string $showId
     * @Route("/remove/{showId}", name="remove")
     * @return Response
     */
    public function remove(string $showId)
    {
        try {
            $this->userShowService->remove($showId);
        } catch (Exception $e) {
            return new JsonResponse(['success' => false], 404);
        }

        return new JsonResponse(['success' => true]);
    }
}
