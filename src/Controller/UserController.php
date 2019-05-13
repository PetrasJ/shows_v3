<?php

namespace App\Controller;

use App\Form\UserType;
use App\Service\Storage;
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
     * @param Storage $storage
     * @Route("/settings", name="settings")
     * @return Response
     */
    public function settings(Storage $storage)
    {
        $user = $storage->getUser();
        $form = $this->createForm(UserType::class, $user);

        return $this->render('user/settings.html.twig', ['form' => $form->createView()]);
    }
}
