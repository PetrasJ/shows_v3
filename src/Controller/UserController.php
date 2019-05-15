<?php

namespace App\Controller;

use App\Form\UserType;
use App\Service\Storage;
use App\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user", name="user_")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class UserController extends AbstractController
{
    /**
     * @param Storage     $storage
     * @param Request     $request
     * @param UserManager $userManager
     * @Route("/settings", name="settings")
     * @return Response
     */
    public function settings(Storage $storage, Request $request, UserManager $userManager)
    {
        $user = $storage->getUser();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->save($user);
            if ($user->getLocale() && $request->getLocale() !== $user->getLocale()) {
                $request->setLocale($user->getLocale());
                return $this->redirect($this->generateUrl('user_settings', ['_locale' => $user->getLocale()]));
            }
        }

        return $this->render('user/settings.html.twig', ['form' => $form->createView()]);
    }
}
