<?php

namespace App\Service;

use App\Entity\Episode;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

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
                ->getUnwatchedEpisodes($this->user, $showId);
        } catch (Exception $e) {
            return null;
        }
    }
}
