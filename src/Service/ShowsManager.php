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

    public function getClient(): Client
    {
        return new Client();
    }

    public function load(bool $update = false): void
    {
        $gap = 0;
        for ($page = 0; $page <= 2000; $page++) {
            try {
                $shows = json_decode($this->getClient()
                    ->get(sprintf('%s?page=%d', self::API_URL, $page))
                    ->getBody()
                );
                if (!$update) {
                    $this->addShows($shows);
                } else {
                    $this->updateShows($shows);
                }
                $gap = 0;
            } catch (Exception $e) {
                if ($gap === 10) {
                    break;
                }
                $gap++;
            }
        }
    }

    public function update(): array
    {
        $shows = $this->entityManager->getRepository(UserShow::class)->getAllUsersShows();

        $updated = [];
        foreach ($shows as $showId) {
            $show = $this->updateShow($showId);
            if ($show) {
                $updated[] = $show;
            };
        }

        $newShows = $this->checkForNewShows();

        return ['updated' => $updated, 'newShows' => $newShows];
    }

    public function find(string $term)
    {
        return $this->entityManager->getRepository(Show::class)->findAllByName($term, false);
    }

    public function findFull(string $term): array
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
            $this->error($e->getMessage(), [__METHOD__]);
        } catch (NoResultException $e) {
            $this->error($e->getMessage(), [__METHOD__]);
        }

        return null;
    }

    public function getNextEpisode($showId)
    {
        try {
            return $this->entityManager
                ->getRepository(Episode::class)
                ->getNextEpisode($this->user, $showId)
                ;
        } catch (Exception $e) {
            $this->error($e->getMessage(), [__METHOD__]);

            return null;
        }
    }

    public function updateShow($showId): ?string
    {
        try {
            $show = json_decode($this->getClient()->get(
                sprintf('%s/%d', self::API_URL, $showId))->getBody()
            );
        } catch (Exception $e) {
            $this->info($e->getMessage(), [__METHOD__]);

            return null;
        }

        $showEntity = $this->addShow($show);

        if ($show->updated === $showEntity->getUpdated()) {
            return null;
        }

        if (isset($show->image->original) && isset($show->image->medium)) {
            $this->imageService->saveShowImage($show->image->original, $show->image->medium, $show->id);
        }

        $this->setShow($showEntity, $show);

        $this->entityManager->persist($showEntity);
        $this->entityManager->flush();

        $this->episodesManager->addEpisodes($showEntity);

        $this->entityManager->persist($showEntity->setUpdated($show->updated));
        $this->entityManager->flush();

        return $show->name;
    }

    private function addShows($shows): void
    {
        foreach ($shows as $show) {
            $this->addShow($show);
        }
    }

    private function updateShows($shows): void
    {
        $count = 0;
        foreach ($shows as $show) {
            $showEntity = $this->addShow($show);
            $this->setShow($showEntity, $show);
            $this->entityManager->persist($showEntity);
            $count++;

            if ($count === 50) {
                $this->entityManager->flush();
                $count = 0;
            }
        }

        $this->entityManager->flush();
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

        $showEntity = new Show();
        $this->setShow($showEntity, $show);

        $this->entityManager->persist($showEntity);
        $this->entityManager->flush();

        return $showEntity;
    }

    private function setShow(Show $showEntity, $show): void
    {
        if (isset($show->image->original)) {
            $showEntity->setImage($show->image->original);
        }
        if (isset($show->image->medium)) {
            $showEntity->setImageMedium($show->image->medium);
        }

        $showEntity
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
    }

    /**
     * @return array
     */
    private function checkForNewShows(): array
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
