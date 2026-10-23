<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\ForgotPasswordType;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use App\Service\Mailer;
use App\Service\UserManager;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/', name: 'app_')]
class SecurityController extends AbstractController
{
    public function __construct(
        private readonly UserManager $userManager,
    ) {
    }

    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): never
    {
        throw new Exception(
            'This method can be blank - it will be intercepted by the logout key on your firewall'
        );
    }

    #[Route('/register', name: 'register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        Security $security,
        LoginFormAuthenticator $authenticator,
        Mailer $mailer,
    ): Response {
        $user = new User();

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setEmailConfirmationToken(
                hash('sha256', $user->getEmail() . $user->getPassword() . time())
            );

            $this->userManager->save($user);
            $mailer->sendConfirmation($user);

            return $security->login($user, $authenticator::class, 'main');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/confirm-email/{token}', name: 'confirm_email')]
    public function confirmEmail(string $token): RedirectResponse
    {
        $user = $this->userManager->getUserByEmailConfirmationToken($token);

        if ($user) {
            $this->addFlash('success', 'email_confirmed');
            $user->setEmailConfirmationToken(null);
            $this->userManager->save($user);
        } else {
            $this->addFlash('danger', 'confirmation_not_found');
        }

        return $this->redirectToRoute('app_login');
    }

    #[Route('/change-password', name: 'change_password')]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        Security $security,
    ): Response {
        /** @var User|null $user */
        $user = $security->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $this->userManager->save($user);
            $this->addFlash('success', 'password_changed');
        }

        return $this->render('security/change-password.html.twig', [
            'changePasswordForm' => $form->createView(),
        ]);
    }

    #[Route('/forgot-password', name: 'forgot_password')]
    public function forgotPassword(Request $request, Mailer $mailer): Response
    {
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $this->userManager->getUserByEmail($email);

            if ($user) {
                $user->setResetPasswordToken(
                    hash('sha256', $user->getEmail() . $user->getPassword() . time())
                );
                $user->setResetPasswordRequestedAt(new DateTime());

                $this->userManager->save($user);
                $mailer->sendResetPassword($user);

                $this->addFlash('success', 'email_sent');
            }
        }

        return $this->render('security/forgot-password.html.twig', [
            'forgotPasswordForm' => $form->createView(),
        ]);
    }

    #[Route('/reset-password/{token}', name: 'reset_password')]
    public function resetPassword(
        string $token,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $user = $this->userManager->getUserByResetPasswordToken($token);

        if (!$user) {
            return $this->render('error.html.twig', [
                'title' => 'reset_password',
                'message' => 'reset_password_token_not_found',
            ]);
        }

        if ($user->getResetPasswordRequestedAt() < (new DateTime())->modify('-1 day')) {
            return $this->render('error.html.twig', [
                'title' => 'reset_password',
                'message' => 'reset_password_token_expired',
            ]);
        }

        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setResetPasswordToken(null);
            $user->setResetPasswordRequestedAt(null);

            $this->userManager->save($user);
            $this->addFlash('success', 'password_changed');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/change-password.html.twig', [
            'changePasswordForm' => $form->createView(),
        ]);
    }
}
