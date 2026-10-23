<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\Mailer;
use App\Service\UserEpisodeManager;
use App\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user', name: 'user_')]
class UserController extends AbstractController
{
    #[Route('/settings', name: 'settings')]
    public function settings(
        Security $security,
        Request $request,
        UserManager $userManager,
        UserEpisodeManager $userEpisodeService,
        Mailer $mailer,
    ): Response {
        /** @var User|null $user */
        $user = $security->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($request->getPayload()->getBoolean('resend_confirmation')
            || $request->query->getBoolean('resend_confirmation')) {
            $mailer->sendConfirmation($user);
            $this->addFlash('success', 'email_sent');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->save($user);

            if ($user->getLocale()) {
                $request->setLocale($user->getLocale());

                return $this->redirectToRoute('user_settings', [
                    '_locale' => $user->getLocale(),
                ]);
            }
        }

        return $this->render('user/settings.html.twig', [
            'form' => $form->createView(),
            'lastEpisodes' => $userEpisodeService->getLastEpisodes(),
            'duration' => $userEpisodeService->getWatchedDuration(),
        ]);
    }
}
