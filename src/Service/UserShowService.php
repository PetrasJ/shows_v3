<?php

namespace App\Service;

use App\Entity\Episode;
use App\Entity\UserShow;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class UserShowService
{
    private $entityManager;
    private $user;

    public function __construct(EntityManagerInterface $entityManager, Storage $storage)
    {
        $this->entityManager = $entityManager;
        $this->user = $storage->getUser();
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
     * @param $status
     * @return array
     */
    public function getShows($status)
    {
        return $this->entityManager
            ->getRepository(UserShow::class)
            ->getShows($this->user, $status)
            ;
    }
}
