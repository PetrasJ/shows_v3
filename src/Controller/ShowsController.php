<?php

namespace App\Controller;

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
    /**
     * @param                 $status
     * @param UserShowService $userShowService
     * @Route("/list/{status}", name="index", defaults={"status":"0"})
     * @return Response
     */
    public function index($status, UserShowService $userShowService)
    {
        $shows = $userShowService->getShows($status);

        return $this->render('shows/index.html.twig', ['shows' => $shows, 'status' => $status]);
    }

    /**
     * @param Request         $request
     * @param UserShowService $userShowService
     * @Route("/update", name="update")
     * @return JsonResponse
     */
    public function update(Request $request, UserShowService $userShowService)
    {
        try {
            $userShowService->updateShow($request->get('id'), ['offset' => $request->get('value')]);
        } catch (NotFoundHttpException $e) {
            return new JsonResponse(['success' => false], 404);
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * @param string          $showId
     * @param UserShowService $userShowService
     * @Route("/add/{showId}", name="add")
     * @return JsonResponse
     */
    public function add(string $showId, UserShowService $userShowService)
    {
        try {
            $userShowService->update($showId, 'add');
        } catch (Exception $e) {
            return new JsonResponse(['success' => false], 404);
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * @param string          $showId
     * @param UserShowService $userShowService
     * @Route("/archive/{showId}", name="archive")
     * @return JsonResponse
     */
    public function archive(string $showId, UserShowService $userShowService)
    {
        try {
            $userShowService->update($showId, 'archive');
        } catch (Exception $e) {
            return new JsonResponse(['success' => false], 404);
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * @param string          $showId
     * @param UserShowService $userShowService
     * @Route("/watch-later/{showId}", name="watch_later")
     * @return JsonResponse
     */
    public function watchLater(string $showId, UserShowService $userShowService)
    {
        try {
            $userShowService->update($showId, 'watch-later');
        } catch (Exception $e) {
            return new JsonResponse(['success' => false], 404);
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * @param string          $showId
     * @param UserShowService $userShowService
     * @Route("/remove/{showId}", name="remove")
     * @return Response
     */
    public function remove(string $showId, UserShowService $userShowService)
    {
        try {
            $userShowService->remove($showId);
        } catch (Exception $e) {
            return new JsonResponse(['success' => false], 404);
        }

        return new JsonResponse(['success' => true]);
    }
}
