<?php

namespace App\Service;

use App\Entity\Episode;
use App\Entity\UserEpisode;
use App\Entity\UserShow;
use App\Traits\LoggerTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserEpisodeService
{
    use LoggerTrait;

    private $entityManager;
    private $user;

    public function __construct(EntityManagerInterface $entityManager, Storage $storage)
    {
        $this->entityManager = $entityManager;
        $this->user = $storage->getUser();
    }

    public function getUnwatchedEpisodes(int $showId)
    {
        try {
            return $this->entityManager
                ->getRepository(Episode::class)
                ->getUnwatchedEpisodes($this->user, $showId)
                ;
        } catch (Exception $e) {
            throw new NotFoundHttpException();
        }
    }

    public function update(int $id, int $userShowId, array $action): void
    {
        $episode = $this->entityManager->find(Episode::class, $id);
        $userEpisode = $this->entityManager
            ->getRepository(UserEpisode::class)
            ->findOneBy(['episode' => $episode, 'userShow' => $userShowId, 'user' => $this->user])
        ;

        if (!$userEpisode) {
            $userShow = $this->entityManager
                ->getRepository(UserShow::class)
                ->findOneBy(['id' => $userShowId, 'user' => $this->user])
            ;
            $userEpisode = (new UserEpisode())
                ->setUser($this->user)
                ->setEpisode($episode)
                ->setShow($episode->getShow())
                ->setUserShow($userShow)
            ;
        }

        if (isset($action['comment'])) {
            $userEpisode->setComment($action['comment']);

            if ($userEpisode->getStatus() !== UserEpisode::STATUS_WATCHED) {
                $userEpisode->setStatus(UserEpisode::STATUS_COMMENTED);
            }
        }

        if (isset($action['watch']) && $action['watch'] === true) {
            $userEpisode->setStatus(UserEpisode::STATUS_WATCHED);
        }

        if (isset($action['unwatch']) && $action['unwatch'] === true) {
            $userEpisode->setStatus(UserEpisode::STATUS_UNWATCHED);
        }

        $this->entityManager->persist($userEpisode);
        $this->entityManager->flush();
    }

    public function getWatchedDuration(): ?int
    {
        try {
            return $this->entityManager->getRepository(UserEpisode::class)->getWatchedDuration($this->user);
        } catch (NonUniqueResultException $e) {
            $this->error($e->getMessage(), [__METHOD__]);

            return null;
        }
    }

    public function getLastEpisodes(): ?array
    {
        return $this->entityManager
            ->getRepository(UserEpisode::class)
            ->getLastEpisodes($this->user, 10)
            ;
    }
}
