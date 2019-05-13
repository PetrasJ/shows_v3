<?php

namespace App\Controller;

use App\Form\SearchShowType;
use App\Service\ShowsManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    private $showsManager;

    public function __construct(ShowsManager $showsManager)
    {
        $this->showsManager = $showsManager;
    }

    /**
     * @param Request $request
     * @Route("/search", name="search")
     * @return JsonResponse
     */
    public function search(Request $request)
    {
        $keyword = $shows = $request->get('term');

        return new JsonResponse($this->showsManager->find($keyword));
    }
}
