<?php

namespace App\Service;

use App\Entity\Episode;
use App\Entity\Show;
use DateTime;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;

class EpisodesManager
{
    private $entityManager;
    private $user;

    public function __construct(EntityManagerInterface $entityManager, Storage $storage)
    {
        $this->entityManager = $entityManager;
        $this->user = $storage->getUser();
    }

    public function addEpisodes(Show $show): int
    {
        $this->removeEpisodes($show);
        $episodes = $this->getEpisodesApi($show);
        $saved = 0;
        foreach ($episodes as $episode) {
            $saved++;
            if (($episode->name) && ($episode->airdate)) {
                $dateTime = (new DateTime())->createFromFormat('Y-m-d\TH:i:s\+00:00', $episode->airstamp);
                $newEpisode = new Episode();
                $newEpisode
                    ->setId($episode->id)
                    ->setShow($show)
                    ->setName($episode->name)
                    ->setSeason($episode->season)
                    ->setEpisode($episode->number)
                    ->setAirstamp($dateTime)
                    ->setAirdate($episode->airdate)
                    ->setAirtime($episode->airtime)
                    ->setSummary($episode->summary)
                    ->setDuration($episode->runtime)
                ;
                $this->entityManager->persist($newEpisode);
            }
        }
        $this->entityManager->flush();

        return $saved;
    }

    /**
     * @param DateTime $from
     * @param DateTime $to
     * @param bool     $watching
     * @return array
     */
    public function getEpisodes(DateTime $from, DateTime $to, $watching = false)
    {
        return $this->entityManager
            ->getRepository(Episode::class)
            ->getEpisodes($from, $to, $this->user, $watching)
            ;
    }

    public function getClient()
    {
        return new Client();
    }

    private function getEpisodesApi(Show $show): array
    {
        return json_decode(
            $this->getClient()
                ->get(sprintf('%s/%d/episodes', ShowsManager::API_URL, $show->getId()))
                ->getBody()
        );
    }

    /**
     * @param Show $show
     */
    private function removeEpisodes(Show $show)
    {
        $episodes = $this->entityManager->getRepository(Episode::class)->findBy(['show' => $show]);

        foreach ($episodes as $episode) {
            $this->entityManager->remove($episode);
        }
        try {
            $this->entityManager->getConnection()->exec('SET FOREIGN_KEY_CHECKS = 0;');
        } catch (DBALException $e) {
        }
        $this->entityManager->flush();
    }
}
