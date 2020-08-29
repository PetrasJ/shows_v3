<?php

namespace App\Tests\Service;

use App\Entity\Show;
use App\Entity\User;
use App\Entity\UserShow;
use App\Repository\UserShowRepository;
use App\Service\EpisodeManager;
use App\Service\ShowManager;
use App\Service\UserShowManager;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\FilterCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Security;

class UserShowManagerTest extends TestCase
{
    /**
     * @param string $type
     * @dataProvider dpUpdate
     */
    public function testUpdate($type)
    {
        $service = $this->getService();

        $service->update(1, $type);
    }

    public function dpUpdate()
    {
        return [
            ['add'],
            ['archive'],
            ['watch-later'],
        ];
    }

    public function testAdd()
    {
        $service = $this->getService();

        $service->add(1);
    }

    public function testRemove()
    {
        $service = $this->getService('remove');

        $service->remove(1);
    }

    public function testGetShows()
    {
        $shows = [
            [
                'id' => 305,
                'name' => 'Black Mirror',
                'summary' => '<p>Over the last ten years, technology has transformed almost every aspect of our lives before we\'ve had time to stop and question it. In every home; on every desk; in every palm - a plasma screen; a monitor; a smartphone--a black mirror of our 21st Century existence. <b>Black Mirror</b> is a contemporary British re-working of <i>The Twilight Zone</i> with stories that tap into the collective unease about our modern world.</p>',
                'status' => 'To Be Determined',
                'userShowId' => 1,
                'offset' => null,
                'userShowStatus' => 1,
                'watched' => '0',
                'imageMedium' => '1',
                'rating' => 5,
            ],
        ];

        $episodes = [
            [
                'userShowId' => 1,
                'id' => 1,
                'season' => 1,
                'episode' => 1,
                'airstamp' => (new DateTime())->modify('-2 days'),
                'name' => 'Weight Gain 4000',
                'duration' => '30',
                'userAirstamp' => '1997-08-21 12:00:00',
                'imageMedium' => '1',
            ],
            [
                'userShowId' => 1,
                'id' => 2,
                'season' => 1,
                'episode' => 2,
                'airstamp' => (new DateTime())->modify('+2 days'),
                'name' => 'Weight Gain 4000',
                'duration' => '30',
                'userAirstamp' => '1997-08-21 12:00:00',
                'imageMedium' => '1',
            ],
        ];

        $service = $this->getService(null, $shows, $episodes);

        $expected = [
            0 =>
                [
                    'id' => 305,
                    'status' => 'To Be Determined',
                    'name' => 'Black Mirror',
                    'episodesCount' => 1,
                    'watchedCount' => '0',
                    'lastEpisode' =>
                        [
                            'userShowId' => 1,
                            'id' => 1,
                            'season' => 1,
                            'episode' => 1,
                            'name' => 'Weight Gain 4000',
                            'duration' => '30',
                            'userAirstamp' => '1997-08-21 12:00:00',
                            'imageMedium' => '1',
                        ],
                    'nextEpisode' =>
                        [
                            'userShowId' => 1,
                            'id' => 2,
                            'season' => 1,
                            'episode' => 2,
                            'name' => 'Weight Gain 4000',
                            'duration' => '30',
                            'userAirstamp' => '1997-08-21 12:00:00',
                            'imageMedium' => '1',
                        ],
                    'offset' => null,
                    'userShowId' => 1,
                    'image' => '1',
                    'rating' => 5,
                ],
        ];

        $result = $service->getShows(UserShow::STATUS_WATCHING);

        unset($result[0]['lastEpisode']['airstamp']);
        unset($result[0]['nextEpisode']['airstamp']);
        $this->assertEquals($expected, $result);
    }

    private function getService($method = 'persist', $shows = null, $episodes = null)
    {
        /** @var EntityManager|MockObject $entityManager */
        $entityManager = $this->createMock(EntityManager::class);
        $filterCollection = $this->createMock(FilterCollection::class);
        $entityManager->method('getFilters')->willReturn($filterCollection);

        $userShowRepo = $this->createMock(UserShowRepository::class);
        $userShowRepo
            ->method('findOneBy')
            ->willReturn((new UserShow())->setShow(new Show()));

        $userShowRepo->method('getShows')->willReturn($shows);
        $userShowRepo->method('getEpisodes')->willReturn($episodes);

        $entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->willReturnOnConsecutiveCalls($userShowRepo, $userShowRepo);

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn(new User());
        /** @var EpisodeManager|MockObject $episodesManager */
        $episodesManager = $this->createMock(EpisodeManager::class);
        /** @var ShowManager|MockObject $showsManager */
        $showsManager = $this->createMock(ShowManager::class);

        $entityManager->method('find')->willReturn(new Show());
        if ($method) {
            $entityManager->expects($this->once())
                ->method($method)
                ->with($this->isInstanceOf(UserShow::class));
        }

        return new UserShowManager($entityManager, $security, $episodesManager, $showsManager);
    }
}
