<?php

namespace App\Service;

use App\Entity\Feedback;
use Doctrine\ORM\EntityManagerInterface;
use Swift_Mailer;
use Swift_Message;

class Mailer
{
    private $entityManager;
    private $mailer;
    private $user;

    public function __construct(EntityManagerInterface $entityManager, Swift_Mailer $mailer, Storage $storage)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->user = $storage->getUser();
    }

    public function sendFeedback(Feedback $feedback)
    {
        $user = $this->user ? $this->user->getId() : '0';
        $message = (new Swift_Message('shows.botai.eu feedback'))
            ->setFrom('no-reply@botai.eu')
            ->setTo('petras.jodkonis@gmail.com')
            ->setBody('name: ' . $feedback->getName() . '\n' .
                'userId: ' . $user . '\n' .
                'email: ' . $feedback->getEmail() . '\n' .
                'message' . $feedback->getMessage()
            )
        ;

        $this->entityManager->persist($feedback);
        $this->entityManager->flush();

        $this->mailer->send($message);
    }
}
