<?php

namespace App\Service;

use App\Entity\Show;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;

class ShowsManager
{
    const API_URL = 'http://api.tvmaze.com/shows';
    private $entityManager;
    private $imageService;
    private $episodesManager;
    private $client;

    public function __construct(
        EntityManagerInterface $entityManager,
        ImageService $imageService,
        EpisodesManager $episodesManager
    )
    {
        $this->entityManager = $entityManager;
        $this->imageService = $imageService;
        $this->episodesManager = $episodesManager;
        $this->client = new Client();
    }

    public function load()
    {
        $gap = 0;
        for ($page = 0; $page <= 1200; $page++) {
            try {
                $shows = $this->client->get(sprintf('%s?page=%d', self::API_URL, $page))->getBody();
                $this->addShows(json_decode($shows));
                $gap = 0;
            } catch (\Exception $e) {
                if ($gap === 10) {
                    break;
                }
                $gap++;
            }
        }
    }

    private function addShows($shows)
    {
        foreach ($shows as $show) {
            $this->addShow($show);
        }
    }

    private function addShow($show): bool
    {
        if (!$show->id) {
            return false;
        }

        if ($this->entityManager->getRepository(Show::class)->findOneBy(['showID' => $show->id])) {
            return false;
        };

        $newShow = new Show();
        $newShow->setShowID($show->id);
        $newShow->setName($show->name);
        $newShow->setUrl($show->url);
        $newShow->setOfficialSite($show->officialSite);
        $newShow->setRating($show->rating->average);
        $this->imageService->saveShowImage($show->image->original, $show->image->medium, $show->id);
        $newShow->setUpdated($show->updated);
        $newShow->setWeight($show->weight);
        $newShow->setStatus($show->status);
        $newShow->setPremiered($show->premiered);
        $newShow->setGenres(json_encode($show->genres));
        if (isset($show->image->original)) {
            $newShow->setImage($show->image->original);
        }
        if (isset($show->image->medium)) {
            $newShow->setImageMedium($show->image->medium);
        }
        $newShow->setSummary($show->summary);
        $this->entityManager->persist($newShow);
        $this->entityManager->flush();
        $this->episodesManager->addEpisodes($show->id);

        return true;
    }
}