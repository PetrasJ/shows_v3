<?php

namespace App\Tests\Service;

use App\Entity\Episode;
use App\Entity\Show;
use App\Repository\EpisodeRepository;
use App\Service\EpisodesManager;
use App\Service\Storage;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EpisodesManagerTest extends TestCase
{
    public function testAddEpisodes()
    {
        $service = $this->getService();
        $show = (new Show())->setId(1);
        $service->addEpisodes($show);
        $service->addEpisodes($show);
    }

    public function testGetEpisodes()
    {
        $service = $this->getService(false);
        $result = $service->getEpisodes(new DateTime(),
            (new DateTime())->modify('+2 days'),
            true);

        $this->assertEquals([], $result);
    }

    private function getEpisodes()
    {
        return '[{"id":277665,"url":"http://www.tvmaze.com/episodes/277665/one-punch-man-1x01-the-strongest-man","name":"The Strongest Man","season":1,"number":1,"airdate":"2015-10-04","airtime":"01:05","airstamp":"2015-10-04T16:05:00+00:00","runtime":25,"image":{"medium":"http://static.tvmaze.com/uploads/images/medium_landscape/77/192602.jpg","original":"http://static.tvmaze.com/uploads/images/original_untouched/77/192602.jpg"},"summary":"","_links":{"self":{"href":"http://api.tvmaze.com/episodes/277665"}}}]';
    }

    private function getService($update = true)
    {
        /** @var EntityManager|MockObject $entityManager */
        $entityManager = $this->createMock(EntityManager::class);
        $connection = $this->createMock(Connection::class);
        if ($update) {
            $connection->expects($this->at(0))->method('exec');
            $connection->expects($this->at(1))->method('exec')->willThrowException(new DBALException());
        }
        $entityManager->method('getConnection')->willReturn($connection);
        $episodesRepo = $this->createMock(EpisodeRepository::class);
        $episodesRepo->method('findBy')->willReturn([new Episode(), new Episode()]);
        $episodesRepo->method('getEpisodesPublic')->willReturn([]);
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
            ->setMethods(['getClient', 'error'])
            ->setConstructorArgs([$entityManager, $storage])
            ->getMock()
        ;
        $service->method('error');
        $service->method('getClient')->willReturn($client);

        if ($update) {
            $entityManager
                ->expects($this->exactly(2))
                ->method('persist')
                ->with($this->isInstanceOf(Episode::class))
            ;
        }

        return $service;
    }
}
