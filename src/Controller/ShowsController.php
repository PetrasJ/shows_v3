<?php

namespace App\Controller;

use App\Service\UserShowService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/shows", name="shows_")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class ShowsController extends AbstractController
{
    /**
     * @param $status
     * @param UserShowService $userShowService
     * @Route("/list/{status}", name="index", defaults={"status":"0"})
     * @return Response
     */
    public function index($status, UserShowService $userShowService)
    {
        $shows = $userShowService->getShows($status);

        return $this->render('shows/index.html.twig', ['shows' => $shows]);
    }

    /**
     * @param Request $request
     * @param UserShowService $userShowService
     * @Route("/update", name="update")
     * @return JsonResponse
     */
    public function update(Request $request, UserShowService $userShowService)
    {
        $userShowService->updateShow($request->get('id'), ['offset' => $request->get('value')]);

        return new JsonResponse(['success' => true]);
    }
}
