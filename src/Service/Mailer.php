<?php

namespace App\Service;

use App\Entity\Feedback;
use App\Entity\User;
use App\Traits\LoggerTrait;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Swift_Mailer;
use Swift_Message;

class Mailer
{
    use LoggerTrait;

    private $entityManager;
    private $mailer;
    private $user;

    public function __construct(EntityManagerInterface $entityManager, Swift_Mailer $mailer, Storage $storage)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->user = $storage->getUser();
    }

    public function sendFeedback(Feedback $feedback): void
    {
        $user = $this->user ? $this->user->getId() : '0';
        $message = (new Swift_Message('shows.botai.eu feedback'))
            ->setFrom('no-reply@botai.eu')
            ->setTo('petras.jodkonis@gmail.com')
            ->setBody('name: ' . $feedback->getName() . PHP_EOL .
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

    public function sendConfirmation(User $user): void
    {
        $message = (new Swift_Message('shows.botai.eu email confirmation'))
            ->setFrom('no-reply@botai.eu')
            ->setTo($user->getEmail())
            ->setBody('test confirmation: ' . PHP_EOL
            . ' https://test.botai.eu/en/confirm-email/' . $user->getEmailConfirmationToken()
            )
        ;

        try {
            $this->mailer->send($message);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
