<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user", name="user_")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/settings", name="settings")
     * @return Response
     */
    public function unwatched()
    {
        return $this->render('user/settings.html.twig');
    }
}
