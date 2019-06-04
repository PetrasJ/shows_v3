<?php

namespace App\Service;

use App\Entity\Episode;
use App\Entity\Show;
use App\Entity\UserEpisode;
use App\Entity\UserShow;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserShowService
{
    private $entityManager;
    private $user;
    private $episodesManager;
    private $showsManager;

    public function __construct(EntityManagerInterface $entityManager, Storage $storage, EpisodesManager $episodesManager, ShowsManager $showsManager)
    {
        $this->entityManager = $entityManager;
        $this->user = $storage->getUser();
        $this->episodesManager = $episodesManager;
        $this->showsManager = $showsManager;
    }

    /**
     * @return array
     */
    public function getShowsWithUnwatchedEpisodes()
    {
        return $this->entityManager
            ->getRepository(Episode::class)
            ->getShowsWithUnwatchedEpisodes($this->user, UserShow::STATUS_WATCHING)
            ;
    }

    /**
     * @param       $userShowId
     * @param array $data
     */
    public function updateShow($userShowId, $data = [])
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

    /**
     * @param $status
     * @return array
     */
    public function getShows($status)
    {
        $shows = $this->entityManager
            ->getRepository(UserShow::class)
            ->getShows($this->user, $status);
        $episodes = $this->entityManager
            ->getRepository(UserShow::class)
            ->getEpisodes($this->user, $status);

        return $this->formatShows($shows, $episodes);
    }

    private function formatShows($shows, $episodes)
    {
        $arr = [];
        foreach ($episodes as $episode) {
            $arr[$episode['userShowId']][] = $episode;
        }

        $formatted = [];
        foreach ($shows as $show)
        {
            if (isset($arr[$show['userShowId']])) {
                $count = 0;
                $lastEpisode = null;
                $nextEpisode = null;
                foreach ($arr[$show['userShowId']] as $episode) {

                    $now = (new DateTime())->modify(sprintf('-%d hours', $show['offset']));
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
                'name' => $show['name'],
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

    /**
     * @param int $userShowId
     * @param int $limit
     * @return UserShow|null
     */
    public function getUserShowAndEpisodes($userShowId, $limit)
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
            return null;
        }

        return $return;
    }

    public function add($showId)
    {
        $show = $this->entityManager->find(Show::class, $showId);
        $userShow = (new UserShow())
            ->setUser($this->user)
            ->setShow($show)
            ->setStatus(UserShow::STATUS_WATCHING)
            ->setOffset(0)
        ;

        $this->entityManager->persist($userShow);
        $this->entityManager->flush();
    }

    /**
     * @param string $userShowId
     * @param string $type
     */
    public function update($userShowId, $type)
    {
        $this->entityManager->getFilters()->disable('softdeleteable');
        $userShow = $this->entityManager
            ->getRepository(UserShow::class)
            ->findOneBy(['user' => $this->user, 'id' => $userShowId])
        ;

        /** @var Show|null $show */
        $show = $userShow->getShow();

        if (!$userShow) {
            $userShow = (new UserShow())
                ->setUser($this->user)
                ->setShow($show)
                ->setOffset(0)
            ;
        }

        $userShow->setDeletedAt(null);
        if ($type === 'add') {
            $userShow->setStatus(UserShow::STATUS_WATCHING);
        } elseif ($type === 'archive') {
            $userShow->setStatus(UserShow::STATUS_ARCHIVED);
        } elseif ($type === 'watch-later') {
            $userShow->setStatus(UserShow::STATUS_WATCH_LATER);
        }

        $this->showsManager->updateShow($userShow->getShow()->getId());
        $this->entityManager->persist($userShow);
        $this->entityManager->flush();
    }

    /**
     * @param string $userShowId
     */
    public function remove($userShowId)
    {
        $userShow = $this->entityManager
            ->getRepository(UserShow::class)
            ->findOneBy(['user' => $this->user, 'id' => $userShowId])
        ;
        $this->entityManager->remove($userShow);
        $this->entityManager->flush();
    }

    /**
     * @param $userShowId
     */
    public function watchAll($userShowId)
    {
        /** @var Show $show */
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
        $userRepo->watchAll($this->user, $userShow);
    }
}
