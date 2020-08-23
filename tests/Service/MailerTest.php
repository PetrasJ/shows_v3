<?php

namespace App\Tests\Service;

use App\Entity\Feedback;
use App\Service\Mailer;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Swift_Mailer;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class MailerTest extends TestCase
{
    public function testSendFeedback()
    {
        /** @var EntityManager|MockObject $entityManager */
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->method('flush');

        /** @var Swift_Mailer|MockObject $swiftMailer */
        $swiftMailer = $this->createMock(Swift_Mailer::class);
        $security = $this->createMock(Security::class);

        /** @var RouterInterface $router */
        $router = $this->createMock(RouterInterface::class);
        /** @var TranslatorInterface $translator */
        $translator = $this->createMock(TranslatorInterface::class);
        $service = new Mailer($entityManager, $swiftMailer, $security, $router, $translator);

        $feedback = new Feedback();

        $swiftMailer->expects($this->once())
            ->method('send')
            ->with($this->isInstanceOf('Swift_Message'));

        $service->sendFeedback($feedback);
    }
}
