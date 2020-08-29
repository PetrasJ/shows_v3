<?php

namespace App\Tests\Service;

use App\Entity\Episode;
use App\Entity\Show;
use App\Entity\User;
use App\Entity\UserEpisode;
use App\Entity\UserShow;
use App\Repository\UserEpisodeRepository;
use App\Repository\UserShowRepository;
use App\Service\UserEpisodeManager;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Security;

class UserEpisodeManagerTest extends TestCase
{
    /**
     * @param array $action
     * @dataProvider dpUpdate
     */
    public function testUpdate($action)
    {
        /** @var EntityManager|MockObject $entityManager */
        $entityManager = $this->createMock(EntityManager::class);

        $episode = (new Episode())->setShow(new Show());
        $entityManager->method('find')->willReturn($episode);
        $userEpisodeRepo = $this->createMock(UserEpisodeRepository::class);
        $userShowRepo = $this->createMock(UserShowRepository::class);
        $userShowRepo->method('findOneBy')->willReturn((new UserShow()));

        $entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->willReturnOnConsecutiveCalls($userEpisodeRepo, $userShowRepo)
        ;

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn(new User());
        $service = new UserEpisodeManager($entityManager, $security);

        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(UserEpisode::class));

        $service->update(1, 1, $action);
    }

    public function dpUpdate()
    {
        return [
            [['watch' => true]],
            [['comment' => '123']],
            [['unwatch' => true]],
        ];
    }
}
