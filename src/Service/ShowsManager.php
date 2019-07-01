<?php

namespace App\Service;

use App\Entity\Episode;
use App\Entity\Show;
use App\Entity\User;
use App\Entity\UserShow;
use App\Traits\LoggerTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use GuzzleHttp\Client;
use Psr\Log\LogLevel;

class ShowsManager
{
    use LoggerTrait;

    const API_URL = 'http://api.tvmaze.com/shows';
    private $entityManager;
    private $imageService;
    private $episodesManager;

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
        $this->user = $storage->getUser();
    }

    public function getClient()
    {
        return new Client();
    }

    public function load()
    {
        $gap = 0;
        for ($page = 0; $page <= 1200; $page++) {
            try {
                $this->addShows(json_decode($this->getClient()
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
        $updated = 0;
        foreach ($shows as $showId) {
            if ($this->updateShow($showId)) {
                $updated++;
            };
        }

        $newShows = $this->checkForNewShows();

        return ['updated' => $updated, 'newShows' => $newShows];
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
        try {
            return $this->entityManager
                ->getRepository(UserShow::class)
                ->getUserShow($this->user, $showId)
                ;
        } catch (NonUniqueResultException $e) {
            $this->error($e->getMessage(), $e->getTrace());
        } catch (NoResultException $e) {
            $this->error($e->getMessage(), $e->getTrace());
        }

        return null;
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
            ->setSummary($show->summary)
        ;

        $this->entityManager->persist($newShow);
        $this->entityManager->flush();

        return $newShow;
    }

    public function updateShow($showId): bool
    {
        try {
            $show = json_decode($this->getClient()->get(
                sprintf('%s/%d', self::API_URL, $showId))->getBody()
            );
        } catch (Exception $e) {
            $this->logger->log(LogLevel::ERROR, $e->getMessage());

            return false;
        }

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
            ->setSummary($show->summary)
        ;


        $this->entityManager->persist($showEntity);
        $this->entityManager->flush();

        $this->episodesManager->addEpisodes($showEntity);

        $this->entityManager->persist($showEntity->setUpdated($show->updated));
        $this->entityManager->flush();

        return true;
    }

    public function getNextEpisode($showId)
    {
        try {
            return $this->entityManager
                ->getRepository(Episode::class)
                ->getNextEpisode($this->user, $showId)
                ;
        } catch (Exception $e) {
            $this->error($e->getMessage(), $e->getTrace());
            return null;
        }
    }

    /**
     * @return array
     */
    private function checkForNewShows()
    {
        $lastShow = $this->entityManager->getRepository(Show::class)->findOneBy([], ['id' => 'desc']);
        $nextShowId = $lastShow ? $lastShow->getId() : 0;
        $saved = [];
        $gap = 0;
        while (true) {
            $nextShowId++;
            try {
                $show = json_decode($this->getClient()
                    ->get(sprintf('%s/%d', self::API_URL, $nextShowId))
                    ->getBody()
                );

                $newShow = $this->addShow($show);
                if ($newShow) {
                    $saved[] = $newShow->getName();
                }

                $gap = 0;
            } catch (Exception $e) {
                $gap++;
                if ($gap === 5) {
                    break;
                }
            }
        }

        return $saved;
    }
}
