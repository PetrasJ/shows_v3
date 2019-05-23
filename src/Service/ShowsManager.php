<?php

namespace App\Service;

use App\Entity\Show;
use App\Entity\User;
use App\Entity\UserShow;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GuzzleHttp\Client;

class ShowsManager
{
    const API_URL = 'http://api.tvmaze.com/shows';
    private $entityManager;
    private $imageService;
    private $episodesManager;
    private $client;

    /**
     * @var User|null
     */
    private $user;

    public function __construct(
        EntityManagerInterface $entityManager,
        ImageService $imageService,
        EpisodesManager $episodesManager,
        Storage $storage
    )
    {
        $this->entityManager = $entityManager;
        $this->imageService = $imageService;
        $this->episodesManager = $episodesManager;
        $this->client = new Client();
        $this->user = $storage->getUser();
    }

    public function load()
    {
        $gap = 0;
        for ($page = 0; $page <= 1200; $page++) {
            try {

                $this->addShows(json_decode($this->client
                    ->get(sprintf('%s?page=%d', self::API_URL, $page))
                    ->getBody()
                ));
                $gap = 0;
            } catch (Exception $e) {
                if ($gap === 10) {
                    break;
                }
                $gap++;
            }
        }
    }

    public function update()
    {
        $shows = $this->entityManager->getRepository(UserShow::class)->getAllUsersShows();
        foreach ($shows as $showId) {
            $this->updateShow($showId);
        }
    }

    public function find(string $term)
    {
        return $this->entityManager->getRepository(Show::class)->findAllByName($term, false);
    }

    public function findFull(string $term)
    {
        $shows = $this->entityManager->getRepository(Show::class)->findAllByName($term, true);
        $userShows = $this->user
            ? $this->entityManager
                ->getRepository(UserShow::class)
                ->getUserShows($this->user, $shows)
            : [];
        return ['shows' => $shows, 'userShows' => $userShows];
    }

    public function getShow($showId)
    {
        return $this->entityManager
            ->getRepository(UserShow::class)
            ->getUserShow($this->user, $showId);
    }

    private function addShows($shows)
    {
        foreach ($shows as $show) {
            $this->addShow($show);
        }
    }

    private function addShow($show): ?Show
    {
        if (!$show->id) {
            return null;
        }

        $showEntity = $this->entityManager->find(Show::class, $show->id);
        if ($showEntity) {
            return $showEntity;
        }

        $newShow = new Show();
        $newShow
            ->setId($show->id)
            ->setName($show->name)
            ->setUrl($show->url)
            ->setOfficialSite($show->officialSite)
            ->setRating($show->rating->average)
            ->setWeight($show->weight)
            ->setStatus($show->status)
            ->setPremiered($show->premiered)
            ->setGenres(json_encode($show->genres))
            ->setSummary($show->summary);

        $this->entityManager->persist($newShow);
        $this->entityManager->flush();

        return $newShow;
    }

    public function updateShow($showId): bool
    {
        $show = json_decode($this->client->get(
            sprintf('%s/%d', self::API_URL, $showId))->getBody()
        );

        $showEntity = $this->addShow($show);

        if ($show->updated === $showEntity->getUpdated()) {
            return false;
        }

        if (isset($show->image->original)) {
            $showEntity->setImage($show->image->original);
        }
        if (isset($show->image->medium)) {
            $showEntity->setImageMedium($show->image->medium);
        }

        if (isset($show->image->original) && isset($show->image->medium)) {
            $this->imageService->saveShowImage($show->image->original, $show->image->medium, $show->id);
        }

        $showEntity
            ->setName($show->name)
            ->setUrl($show->url)
            ->setOfficialSite($show->officialSite)
            ->setRating($show->rating->average)
            ->setWeight($show->weight)
            ->setStatus($show->status)
            ->setPremiered($show->premiered)
            ->setGenres(json_encode($show->genres))
            ->setSummary($show->summary);


        $this->entityManager->persist($showEntity);
        $this->entityManager->flush();

        $this->episodesManager->addEpisodes($showEntity);

        $this->entityManager->persist($showEntity->setUpdated($show->updated));
        $this->entityManager->flush();

        return true;
    }
}
