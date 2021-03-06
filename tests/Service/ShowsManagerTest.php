<?php

namespace App\Tests\Service;

use App\Entity\Show;
use App\Repository\ShowRepository;
use App\Repository\UserShowRepository;
use App\Service\EpisodeManager;
use App\Service\ImageService;
use App\Service\ShowManager;
use App\Service\TVMazeClient;
use Doctrine\ORM\EntityManager;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Security;

class ShowsManagerTest extends TestCase
{
    public function testUpdate()
    {
        /** @var EntityManager|MockObject $entityManager */
        $entityManager = $this->createMock(EntityManager::class);
        $imageService = $this->createMock(ImageService::class);
        $episodeManager = $this->createMock(EpisodeManager::class);

        $userShowRepo = $this->createMock(UserShowRepository::class);
        $userShowRepo->method('getAllUsersShows')->willReturn([1]);
        $showRepo = $this->createMock(ShowRepository::class);
        $show = (new Show())->setId(1);
        $showRepo->method('findOneBy')->willReturn($show);

        $entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->willReturnOnConsecutiveCalls($userShowRepo, $showRepo)
        ;

        $client = $this->createMock(TVMazeClient::class);
        $client->method('getShow')->willReturn($this->getShow());

        $security = $this->createMock(Security::class);

        $service = new ShowManager($entityManager, $imageService, $episodeManager, $security, $client);

        $client->expects($this->at(0))->method('getShow')->willReturn($this->getShow());
        $client->expects($this->at(1))->method('getShow')->willReturn($this->getShow());
        $client->expects($this->at(2))->method('getShow')->willThrowException(new Exception());
        $client->expects($this->at(3))->method('getShow')->willThrowException(new Exception());
        $client->expects($this->at(4))->method('getShow')->willThrowException(new Exception());
        $client->expects($this->at(5))->method('getShow')->willThrowException(new Exception());
        $client->expects($this->at(6))->method('getShow')->willThrowException(new Exception());

        $service->update();
    }

    private function getShow()
    {
        return json_decode('{"id":1,"url":"http://www.tvmaze.com/shows/1/under-the-dome","name":"Under the Dome","type":"Scripted","language":"English","genres":["Drama","Science-Fiction","Thriller"],"status":"Ended","runtime":60,"premiered":"2013-06-24","officialSite":"http://www.cbs.com/shows/under-the-dome/","schedule":{"time":"22:00","days":["Thursday"]},"rating":{"average":6.6},"weight":90,"network":{"id":2,"name":"CBS","country":{"name":"United States","code":"US","timezone":"America/New_York"}},"webChannel":null,"externals":{"tvrage":25988,"thetvdb":264492,"imdb":"tt1553656"},"image":{"medium":"http://static.tvmaze.com/uploads/images/medium_portrait/81/202627.jpg","original":"http://static.tvmaze.com/uploads/images/original_untouched/81/202627.jpg"},"summary":"<p><b>Under the Dome</b> is the story of a small town that is suddenly and inexplicably sealed off from the rest of the world by an enormous transparent dome. The town\'s inhabitants must deal with surviving the post-apocalyptic conditions while searching for answers about the dome, where it came from and if and when it will go away.</p>","updated":1558460639,"_links":{"self":{"href":"http://api.tvmaze.com/shows/1"},"previousepisode":{"href":"http://api.tvmaze.com/episodes/185054"}}}');
    }
}
