<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class GeneralController extends AbstractController
{
    protected function fail(): Response
    {
        return new Response('', 500);
    }

    protected function failJson(): JsonResponse
    {
        return new JsonResponse(['success' => false], 500);
    }

    protected function successJson(): JsonResponse
    {
        return new JsonResponse(['success' => true]);
    }
}
