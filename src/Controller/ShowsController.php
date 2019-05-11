<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ShowsController
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class ShowsController extends AbstractController
{
    /**
     * @Route("/unwatched", name="unwatched")
     * @return Response
     */
    public function search()
    {
        return $this->render('shows/index.html.twig');
    }
}
