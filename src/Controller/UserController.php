<?php

namespace App\Controller;

use App\Form\UserType;
use App\Service\Mailer;
use App\Service\UserEpisodeManager;
use App\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/user", name="user_")
 */
class UserController extends AbstractController
{
    /**
     * @param Request $request
     * @param UserManager $userManager
     * @param UserEpisodeManager $userEpisodeService
     * @param Mailer $mailer
     * @Route("/settings", name="settings")
     * @return Response
     */
    public function settings(
        Security $security,
        Request $request,
        UserManager $userManager,
        UserEpisodeManager $userEpisodeService,
        Mailer $mailer
    ) {
        $user = $security->getUser();
        if ($request->get('resend_confirmation')) {
            $mailer->sendConfirmation($user);
            $this->addFlash('success', 'email_sent');
        }
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->save($user);
            if ($user->getLocale()) {
                $request->setLocale($user->getLocale());

                return $this->redirect($this->generateUrl('user_settings', ['_locale' => $user->getLocale()]));
            }
        }

        return $this->render('user/settings.html.twig',
            [
                'form' => $form->createView(),
                'lastEpisodes' => $userEpisodeService->getLastEpisodes(),
                'duration' => $userEpisodeService->getWatchedDuration(),
            ]
        );
    }
}
