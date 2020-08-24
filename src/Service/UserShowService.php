<?php

namespace App\Service;

use App\Entity\Episode;
use App\Entity\Show;
use App\Entity\UserEpisode;
use App\Entity\UserShow;
use App\Traits\LoggerTrait;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class UserShowService
{
    use LoggerTrait;

    private EntityManagerInterface $entityManager;
    private ?UserInterface $user;
    private EpisodeManager $episodeManager;
    private ShowManager $showManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security,
        EpisodeManager $episodeManager,
        ShowManager $showManager
    ) {
        $this->entityManager = $entityManager;
        $this->user = $security->getUser();
        $this->episodeManager = $episodeManager;
        $this->showManager = $showManager;
    }

    public function getShowsWithUnwatchedEpisodes(): ?array
    {
        return $this->entityManager
            ->getRepository(Episode::class)
            ->getShowsWithUnwatchedEpisodes($this->user, UserShow::STATUS_WATCHING)
            ;
    }

    public function updateShow(int $userShowId, array $data = []): void
    {
        $userShow = $this->entityManager
            ->getRepository(UserShow::class)
            ->findOneBy(['id' => $userShowId, 'user' => $this->user])
        ;

        if (!$userShow) {
            throw new NotFoundHttpException();
        }

        $userShow->setOffset($data['offset']);
        $this->entityManager->persist($userShow);
        $this->entityManager->flush();
    }

    public function getShows(int $status): ?array
    {
        $shows = $this->entityManager
            ->getRepository(UserShow::class)
            ->getShows($this->user, $status)
        ;
        $episodes = $this->entityManager
            ->getRepository(UserShow::class)
            ->getEpisodes($this->user, $status)
        ;

        return $this->formatShows($shows, $episodes);
    }

    private function formatShows($shows, $episodes): ?array
    {
        $showEpisodes = [];
        foreach ($episodes as $episode) {
            $showEpisodes[$episode['userShowId']][] = $episode;
        }

        $formatted = [];
        foreach ($shows as $show) {
            $count = 0;
            $lastEpisode = null;
            $nextEpisode = null;
            if (isset($showEpisodes[$show['userShowId']])) {
                $now = (new DateTime())->modify(sprintf('-%d hours', $show['offset']));
                foreach ($showEpisodes[$show['userShowId']] as $episode) {
                    if ($episode['airstamp'] < $now) {
                        $count++;
                        $lastEpisode = $episode;

                    } else {
                        $nextEpisode = $episode;

                        break;
                    }
                }
            }

            $formatted[] = [
                'id' => $show['id'],
                'status' => $show['status'],
                'rating' => $show['rating'],
                'name' => $show['name'],
                'image' => $show['imageMedium'],
                'episodesCount' => $count,
                'watchedCount' => $show['watched'],
                'lastEpisode' => $lastEpisode,
                'nextEpisode' => $nextEpisode,
                'offset' => $show['offset'],
                'userShowId' => $show['userShowId'],
            ];
        }

        return $formatted;
    }

    public function getUserShowAndEpisodes(int $userShowId, int $limit): ?array
    {
        try {
            $userShow = $this->entityManager->find(UserShow::class, $userShowId);

            $return['episodes'] = $this->entityManager
                ->getRepository(Episode::class)
                ->getUserShowEpisodes($this->user, $userShow, $limit)
            ;

            $return['userShow'] = $this->entityManager
                ->getRepository(UserShow::class)
                ->getUserShow($this->user, $userShowId)
            ;

        } catch (Exception $e) {
            $this->error($e->getMessage(), [__METHOD__]);

            return null;
        }

        return $return;
    }

    public function getUserShow(int $userShowId): ?array
    {
        try {
            return $this->entityManager
                ->getRepository(UserShow::class)
                ->getUserShow($this->user, $userShowId)
                ;
        } catch (Exception $e) {
            $this->error($e->getMessage(), [__METHOD__]);

            return null;
        }
    }

    public function add(int $showId): void
    {
        $show = $this->entityManager->find(Show::class, $showId);
        $userShow = (new UserShow())
            ->setUser($this->user)
            ->setShow($show)
            ->setStatus(UserShow::STATUS_WATCHING)
            ->setOffset(0)
        ;

        $this->showManager->updateShow($showId);
        $this->entityManager->persist($userShow);
        $this->entityManager->flush();
    }

    public function update(int $userShowId, string $type): void
    {
        $this->entityManager->getFilters()->disable('softdeleteable');
        /** @var UserShow $userShow */
        $userShow = $this->entityManager
            ->getRepository(UserShow::class)
            ->findOneBy(['user' => $this->user, 'id' => $userShowId])
        ;

        $userShow->setDeletedAt(null);
        if ($type === 'add') {
            $userShow->setStatus(UserShow::STATUS_WATCHING);
        } elseif ($type === 'archive') {
            $userShow->setStatus(UserShow::STATUS_ARCHIVED);
        } elseif ($type === 'watch-later') {
            $userShow->setStatus(UserShow::STATUS_WATCH_LATER);
        }

        $this->showManager->updateShow($userShow->getShow()->getId());
        $this->entityManager->persist($userShow);
        $this->entityManager->flush();
    }

    public function remove(int $userShowId): void
    {
        $userShow = $this->entityManager
            ->getRepository(UserShow::class)
            ->findOneBy(['user' => $this->user, 'id' => $userShowId])
        ;
        $this->entityManager->remove($userShow);
        $this->entityManager->flush();
    }

    public function watchAll(int $userShowId): void
    {
        /** @var UserShow $userShow */
        $userShow = $this->entityManager
            ->getRepository(UserShow::class)
            ->findOneBy(['user' => $this->user, 'id' => $userShowId])
        ;

        $episodes = $this->entityManager
            ->getRepository(Episode::class)
            ->getUnwatchedEpisodeEntities($this->user, $userShow)
        ;
        foreach ($episodes as $episode) {
            /** @var Episode $episode */
            $this->entityManager
                ->persist((new UserEpisode())
                    ->setUser($this->user)
                    ->setShow($userShow->getShow())
                    ->setUserShow($userShow)
                    ->setEpisode($episode));
        }
        $this->entityManager->flush();

        $userRepo = $this->entityManager->getRepository(UserEpisode::class);
        $upcomingEpisodes = $userRepo->getUpcomingUpdatedEpisodes($userShow);
        foreach ($upcomingEpisodes as $episode) {
            $this->entityManager->remove($episode);
        }
        $this->entityManager->flush();

        $userRepo->watchAll($userShow);
    }
}
