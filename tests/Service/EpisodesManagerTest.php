<?php

namespace App\Tests\Service;

use App\Entity\Episode;
use App\Entity\Show;
use App\Repository\EpisodeRepository;
use App\Service\EpisodesManager;
use App\Service\Storage;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EpisodesManagerTest extends TestCase
{
    public function testAddEpisodes()
    {
        /** @var EntityManager|MockObject $entityManager */
        $entityManager = $this->createMock(EntityManager::class);

        $connection = $this->createMock(Connection::class);
        $connection->method('exec');
        $entityManager->method('getConnection')->willReturn($connection);

        $episodesRepo = $this->createMock(EpisodeRepository::class);
        $episodesRepo->method('findBy')->willReturn([new Episode(), new Episode()]);

        $entityManager->method('getRepository')->with(Episode::class)->willReturn($episodesRepo);

        $storage = new Storage();

        $client = $this->getMockBuilder(Client::class)
            ->setMethods(['get'])
            ->getMock()
        ;

        $response = $this->createMock(Response::class);
        $response->method('getBody')->willReturn($this->getEpisodes());

        $client->method('get')->willReturn($response);

        /** @var EpisodesManager|MockObject $service */
        $service = $this->getMockBuilder(EpisodesManager::class)
            ->setMethods(['getClient'])
            ->setConstructorArgs([$entityManager, $storage])
            ->getMock();

        $service->method('getClient')->willReturn($client);

        $show = (new Show())->setId(1);

        $entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Episode::class));

        $service->addEpisodes($show);
    }

    public function getEpisodes()
    {
        return '[{"id":277665,"url":"http://www.tvmaze.com/episodes/277665/one-punch-man-1x01-the-strongest-man","name":"The Strongest Man","season":1,"number":1,"airdate":"2015-10-04","airtime":"01:05","airstamp":"2015-10-04T16:05:00+00:00","runtime":25,"image":{"medium":"http://static.tvmaze.com/uploads/images/medium_landscape/77/192602.jpg","original":"http://static.tvmaze.com/uploads/images/original_untouched/77/192602.jpg"},"summary":"","_links":{"self":{"href":"http://api.tvmaze.com/episodes/277665"}}}]';
    }
}
