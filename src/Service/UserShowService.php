<?php

namespace App\Service;

use App\Entity\Episode;
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
        try {
            return $this->entityManager
                ->getRepository(Episode::class)
                ->getShowsWithUnwatchedEpisodes($this->user);
        } catch (Exception $e) {
            return null;
        }
    }
}
