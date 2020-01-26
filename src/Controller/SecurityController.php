<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\ForgotPasswordType;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use App\Service\Mailer;
use App\Service\Storage;
use App\Service\UserManager;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route("/", name="app_")
 */
class SecurityController extends AbstractController
{
    /**
     * @var UserManager
     */
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @Route("/login", name="login")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="logout")
     * @throws Exception
     */
    public function logout()
    {
        throw new Exception(
            'This method can be blank - it will be intercepted by the logout key on your firewall'
        );
    }

    /**
     * @Route("/register", name="register")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param GuardAuthenticatorHandler $guardHandler
     * @param LoginFormAuthenticator $authenticator
     * @param Mailer $mailer
     * @return Response
     */
    public function register(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $authenticator,
        Mailer $mailer
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setEmailConfirmationToken(hash('sha256', $user->getEmail() . $user->getPassword() . time()));
            $this->userManager->save($user);
            $mailer->sendConfirmation($user);

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/confirm-email/{token}", name="confirm_email")
     * @param string $token
     * @return RedirectResponse
     * @throws Exception
     */
    public function confirmEmail($token)
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

    /**
     * @Route("/change-password", name="change_password")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param Storage $storage
     * @return Response
     */
    public function changePassword(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        Storage $storage
    ): Response {
        $user = $storage->getUser();
        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordEncoder->encodePassword(
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

    /**
     * @Route("/forgot-password", name="forgot_password")
     * @param Request $request
     * @param Mailer $mailer
     * @return Response
     * @throws Exception
     */
    public function forgotPassword(Request $request, Mailer $mailer): Response
    {
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->userManager->getUserByEmail($request->get('forgot_password')['email']);
            if ($user) {
                $user->setResetPasswordToken(hash('sha256', $user->getEmail() . $user->getPassword() . time()));
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

    /**
     * @Route("/reset-password/{token}", name="reset_password")
     * @param $token
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response|RedirectResponse
     * @throws Exception
     */
    public function resetPassword(
        $token,
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder
    ): Response {
        $user = $this->userManager->getUserByResetPasswordToken($token);
        if ($user) {
            if ($user->getResetPasswordRequestedAt() < (new DateTime())->modify('-1 day')) {
                return $this->render(
                    'error.html.twig',
                    ['title' => 'reset_password', 'message' => 'reset_password_token_expired']
                );
            }
            $form = $this->createForm(ChangePasswordType::class, $user);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                $this->userManager->save($user);
                $this->addFlash('success', 'password_changed');

                return $this->redirectToRoute('app_login');
            }

            return $this->render('security/change-password.html.twig', [
                'changePasswordForm' => $form->createView(),
            ]);
        } else {
            return $this->render(
                'error.html.twig',
                ['title' => 'reset_password', 'message' => 'reset_password_token_not_found']
            );
        }
    }
}
