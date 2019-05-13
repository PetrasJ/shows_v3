<?php

namespace App\Service;

use App\Entity\Episode;
use App\Entity\UserEpisode;
use App\Entity\UserShow;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserEpisodeService
{
    private $entityManager;
    private $user;

    public function __construct(EntityManagerInterface $entityManager, Storage $storage)
    {
        $this->entityManager = $entityManager;
        $this->user = $storage->getUser();
    }

    public function getUnwatchedEpisodes($showId)
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

    public function update($id, $action)
    {
        $episode = $this->entityManager->find(Episode::class, $id);
        $userEpisode = $this->entityManager
            ->getRepository(UserEpisode::class)
            ->findOneBy(['episodeID' => $id, 'user' => $this->user])
        ;

        if (!$userEpisode) {
            $userShow = $this->entityManager
                ->getRepository(UserShow::class)
                ->findOneBy(['show' => $episode->getShow(), 'user' => $this->user])
            ;
            $userEpisode = (new UserEpisode())
                ->setUser($this->user)
                ->setEpisodeID($id)
                ->setShow($episode->getShow())
                ->setUserShow($userShow)
            ;
        }

        if (isset($action['comment'])) {
            $userEpisode->setComment($action['comment'])->setStatus(UserEpisode::STATUS_COMMENTED);
        }

        if (isset($action['watch']) && $action['watch'] === true)
        {
            $userEpisode->setStatus(UserEpisode::STATUS_WATCHED);
        }

        $this->entityManager->persist($userEpisode);
        $this->entityManager->flush();
    }
}
