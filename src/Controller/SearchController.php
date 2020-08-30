<?php

namespace App\Controller;

use App\Service\ShowManager;
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
    private ShowManager $showManager;

    public function __construct(ShowManager $showManager)
    {
        $this->showManager = $showManager;
    }

    /**
     * @param Request $request
     * @Route("/", name="search")
     * @return JsonResponse
     */
    public function search(Request $request)
    {
        return new JsonResponse($this->showManager->find($request->get('term')));
    }

    /**
     * @param string $term
     * @param ShowManager $showManager
     * @Route("/results/{term}", name="results", defaults={"term":null})
     * @return Response
     */
    public function results($term, ShowManager $showManager)
    {
        $shows = $showManager->findFull($term);

        return $this->render('search/results.html.twig', [
            'shows' => $shows['shows'],
            'userShows' => $shows['userShows'],
            'term' => $term,
        ]);
    }
}
