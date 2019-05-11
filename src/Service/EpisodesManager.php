<?php

namespace App\Service;

use App\Entity\Episode;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;

class EpisodesManager
{
    private $entityManager;
    private $client;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->client = new Client();
    }

    public function addEpisodes(int $showID)
    {
        $this->removeEpisodes($showID);
        $episodes = $this->getEpisodes($showID);

        $saved = 0;
        foreach ($episodes as $episode) {
            $saved++;
            if (($episode->name) && ($episode->airdate)) {
                $newEpisode = new Episode();
                $newEpisode->setShowID($showID);
                $newEpisode->setEpisodeID($episode->id);
                $newEpisode->setName($episode->name);
                $newEpisode->setSeason($episode->season);
                $newEpisode->setEpisode($episode->number);
                $newEpisode->setAirdate($episode->airdate);
                $newEpisode->setAirtime($episode->airtime);
                $newEpisode->setSummary($episode->summary);
                $newEpisode->setDuration($episode->runtime);
                $this->entityManager->persist($newEpisode);
            }
        }
        $this->entityManager->flush();

        return $saved;
    }

    private function getEpisodes(int $showID): array
    {
        return json_decode(
            $this->client->get(sprintf('%s/%d/episodes', ShowsManager::API_URL, $showID))->getBody()
        );
    }

    private function removeEpisodes(int $showID)
    {
        $episodes = $this->entityManager->getRepository(Episode::class)->findBy(['showID' => $showID]);
        foreach ($episodes as $episode)
        {
            $this->entityManager->remove($episode);
        }

        $this->entityManager->flush();
    }
}
