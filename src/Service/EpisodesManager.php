<?php

namespace App\Service;

use App\Entity\Episode;
use App\Entity\Show;
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

    public function addEpisodes(Show $show): int
    {
        $this->removeEpisodes($show);
        $episodes = $this->getEpisodes($show);

        $saved = 0;
        foreach ($episodes as $episode) {
            $saved++;
            if (($episode->name) && ($episode->airdate)) {
                $newEpisode = new Episode();
                $newEpisode
                    ->setId($episode->id)
                    ->setShow($show)
                    ->setName($episode->name)
                    ->setSeason($episode->season)
                    ->setEpisode($episode->number)
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

    private function getEpisodes(Show $show): array
    {
        return json_decode(
            $this->client
                ->get(sprintf('%s/%d/episodes', ShowsManager::API_URL, $show->getId()))
                ->getBody()
        );
    }

    private function removeEpisodes(Show $show)
    {
        $episodes = $this->entityManager->getRepository(Episode::class)->findBy(['show' => $show]);
        foreach ($episodes as $episode) {
            $this->entityManager->remove($episode);
        }

        $this->entityManager->flush();
    }
}
