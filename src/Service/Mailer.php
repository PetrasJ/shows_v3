<?php

namespace App\Service;

use App\Entity\Feedback;
use App\Entity\User;
use App\Traits\LoggerTrait;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class Mailer
{
    use LoggerTrait;

    private EntityManagerInterface $entityManager;

    private ?UserInterface $user;
    private RouterInterface $router;
    private TranslatorInterface $translator;
    private MailerInterface $mailer;

    public function __construct(
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        Security $security,
        RouterInterface $router,
        TranslatorInterface $translator
    ) {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->user = $security->getUser();
        $this->router = $router;
        $this->translator = $translator;
    }

    public function sendFeedback(Feedback $feedback): void
    {
        $user = $this->user ? $this->user->getId() : '0';
        $message = (new Email())
            ->from('no-reply@botai.eu')
            ->to('petras.jodkonis@gmail.com')
            ->subject('shows.botai.eu feedback')
            ->html('name: ' . $feedback->getName() . PHP_EOL .
                'userId: ' . $user . PHP_EOL .
                'email: ' . $feedback->getEmail() . PHP_EOL .
                'message: ' . $feedback->getMessage()
            )
        ;

        $this->entityManager->persist($feedback);
        $this->entityManager->flush();

        try {
            $this->mailer->send($message);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function sendConfirmation(UserInterface $user): void
    {
        $url = sprintf(
            '%s://%s%s',
            $this->router->getContext()->getScheme(),
            $this->router->getContext()->getHost(),
            $this->router->generate('app_confirm_email', ['token' => $user->getEmailConfirmationToken()])
        );
        $message = (new Email())
            ->from('no-reply@botai.eu')
            ->to($user->getEmail())
            ->subject('shows.botai.eu email confirmation')
            ->html($this->translator->trans('confirm_email') . ': ' . PHP_EOL . $url)
        ;

        try {
            $this->mailer->send($message);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function sendResetPassword(User $user): void
    {
        $url = sprintf(
            '%s://%s%s',
            $this->router->getContext()->getScheme(),
            $this->router->getContext()->getHost(),
            $this->router->generate('app_reset_password', ['token' => $user->getResetPasswordToken()])
        );
        $message = (new Email())
            ->from('no-reply@botai.eu')
            ->to($user->getEmail())
            ->subject('shows.botai.eu reset password')
            ->body($this->translator->trans('reset_password') . ': ' . PHP_EOL . $url)
        ;

        try {
            $this->mailer->send($message);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
