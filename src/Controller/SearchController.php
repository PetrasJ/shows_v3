<?php

namespace App\Controller;

use App\Service\ShowsManager;
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
        return new JsonResponse($this->showsManager->find($request->get('term')));
    }

    /**
     * @param string       $term
     * @param ShowsManager $showsManager
     * @Route("/results/{term}", name="results")
     * @return Response
     */
    public function results($term, ShowsManager $showsManager)
    {
        $shows = $showsManager->findFull($term);

        return $this->render('search/results.html.twig', [
            'shows' => $shows['shows'],
            'userShows' => $shows['userShows'],
            'term' => $term,
        ]);
    }
}
