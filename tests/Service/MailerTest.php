<?php

namespace App\Tests\Service;

use App\Entity\Feedback;
use App\Service\Mailer;
use App\Service\Storage;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Swift_Mailer;

class MailerTest extends TestCase
{
    public function testSendFeedback()
    {
        /** @var EntityManager|MockObject $entityManager */
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->method('flush');

        /** @var Swift_Mailer|MockObject $swiftMailer */
        $swiftMailer = $this->createMock(Swift_Mailer::class);
        $storage = new Storage();
        $service = new Mailer($entityManager, $swiftMailer, $storage);

        $feedback = new Feedback();

        $swiftMailer->expects($this->once())
            ->method('send')
            ->with($this->isInstanceOf('Swift_Message'));

        $service->sendFeedback($feedback);
    }
}
