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
     * @Route("/", name="search")
     */
    public function search(Request $request): JsonResponse
    {
        return new JsonResponse($this->showManager->find($request->get('term')));
    }

    /**
     * @Route("/results/{term}", name="results", defaults={"term":null})
     */
    public function results(string $term, ShowManager $showManager): Response
    {
        [$shows, $userShows] = $showManager->findFull($term);

        return $this->render('search/results.html.twig', [
            'shows' => $shows,
            'userShows' => $userShows,
            'term' => $term,
        ]);
    }
}
