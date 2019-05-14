<?php

namespace App\Controller;

use App\Service\ShowsManager;
use App\Service\UserShowService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/search", name="search_")
 */
class SearchController extends AbstractController
{
    private $showsManager;

    public function __construct(ShowsManager $showsManager)
    {
        $this->showsManager = $showsManager;
    }

    /**
     * @param Request $request
     * @Route("/", name="search")
     * @return JsonResponse
     */
    public function search(Request $request)
    {
        $keyword = $shows = $request->get('term');

        return new JsonResponse($this->showsManager->find($keyword));
    }

    /**
     * @param string       $string
     * @param ShowsManager $showsManager
     * @Route("/select/{string}", name="select")
     * @return Response
     */
    public function select($string, ShowsManager $showsManager)
    {
        $shows = $showsManager->findFull($string);

        return $this->render('search/results.html.twig', [
            'shows' => $shows['shows'],
            'userShows' => $shows['userShows']]);
    }

    /**
     * @param string          $id
     * @param UserShowService $userShowService
     * @Route("/add/{id}", name="add")
     * @return Response
     */
    public function add($id, UserShowService $userShowService)
    {
     //   try {
            $userShowService->update($id, 'add');
   /*     } catch (Exception $e) {
            return new JsonResponse(['success' => false], 404);
        }*/

        return new JsonResponse(['success' => true]);
    }

    /**
     * @param string          $id
     * @param UserShowService $userShowService
     * @Route("/remove/{id}", name="remove")
     * @return Response
     */
    public function remove($id, UserShowService $userShowService)
    {
        try {
            $userShowService->remove($id);
        } catch (Exception $e) {
            return new JsonResponse(['success' => false], 404);
        }

        return new JsonResponse(['success' => true]);
    }
}
