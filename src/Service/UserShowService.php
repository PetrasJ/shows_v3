<?php

namespace App\Service;

use App\Entity\Episode;
use App\Entity\Show;
use App\Entity\UserShow;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
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
     * @param       $id
     * @param array $data
     */
    public function updateShow($id, $data = [])
    {
        try {
            /** @var Show $show */
            $show = $this->entityManager->getReference(Show::class, $id);
            $userShow = $this->entityManager
                ->getRepository(UserShow::class)
                ->findOneBy(['show' => $show, 'user' => $this->user])
            ;
        } catch (ORMException $e) {
            throw new NotFoundHttpException();
        }

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
            ->getShows($this->user, $status)
        ;

        return $this->formatShows($shows);
    }

    /**
     * @param $showId
     * @return UserShow|null
     */
    public function getShow($showId)
    {
        try {
        $show = $this->entityManager->getReference(Show::class, $showId);

        return $this->entityManager
            ->getRepository(UserShow::class)
            ->findOneBy(['user' => $this->user, 'show' => $show])
            ;
        } catch (OrmException $e)
        {
            return null;
        }
    }

    /**
     * @param string $id
     * @param string $type
     * @throws ORMException
     */
    public function update($id, $type)
    {
        /** @var Show|null $show */
        $show = $this->entityManager->getReference(Show::class, $id);
        $this->entityManager->getFilters()->disable('softdeleteable');
        $userShow = $this->entityManager
            ->getRepository(UserShow::class)
            ->findOneBy(['user' => $this->user, 'show' => $show])
        ;

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

        $this->showsManager->updateShow($id);
        $this->entityManager->persist($userShow);
        $this->entityManager->flush();
    }

    /**
     * @param string $id
     * @throws ORMException
     */
    public function remove($id)
    {
        $show = $this->entityManager->getReference(Show::class, $id);
        $userShow = $this->entityManager
            ->getRepository(UserShow::class)
            ->findOneBy(['user' => $this->user, 'show' => $show])
        ;
        $this->entityManager->remove($userShow);
        $this->entityManager->flush();
    }

    private function formatShows($shows): array
    {
        $formatted = [];
        foreach ($shows as $userShow) {
            /** @var UserShow $userShow */
            $episodes = $userShow->getShow()->getEpisodes();

            $lastEpisode = null;
            $nextEpisode = null;
            $now = (new DateTime())->modify(sprintf('-%d hours', $userShow->getOffset()));

            $count = 0;
            foreach ($episodes as $episode) {
                /** @var Episode $episode */
                $date = $episode->getAirstamp();
                if ($date < $now) {
                    $count++;
                    $lastEpisode = $episode
                        ->setModifiedDate($date->modify(sprintf('+%d hours', $userShow->getOffset())));
                } else {
                    $nextEpisode = $episode
                        ->setModifiedDate($date->modify(sprintf('+%d hours', $userShow->getOffset())));
                    break;
                }
            }

            $formatted[] = [
                'id' => $userShow->getShow()->getId(),
                'status' => $userShow->getShow()->getStatus(),
                'name' => $userShow->getShow()->getName(),
                'episodesCount' => $count,
                'watchedCount' => $userShow->getUserEpisodes()->count(),
                'lastEpisode' => $lastEpisode,
                'nextEpisode' => $nextEpisode,
                'offset' => $userShow->getOffset(),
            ];
        }

        return $formatted;
    }
}
