<?php

namespace App\Service;

use App\Entity\Episode;
use App\Entity\Show;
use App\Entity\User;
use App\Entity\UserShows;
use App\Repository\EpisodeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;

class UserShowsService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return array
     */
    public function getShowsWithUnwatchedEpisodes()
    {
        $result = [];
        $resultCount = [];
        $upcomingEpisode = [];
        try {
        $repository = $this->entityManager->getRepository(UserShows::class);
        $userShows = $repository->getShows(1);

        $showRepo = $this->entityManager->getRepository(Show::class);
        $show = $showRepo->findOneBy(['showID'=>7480]);
        $episodeRepo = $this->entityManager->getRepository(Episode::class);
        $episode = $episodeRepo->findOneBy(['episodeID' => 1217060]);

        foreach ($userShows as $userShow) {
            $checkShow = $this->checkShowForUnwatchedEpisodes($userShow);
            if ($checkShow) {
                $result[] = $userShow;
                $resultCount[$userShow] = count($checkShow);
                $upcomingEpisode[$userShow] = end($checkShow);
            }
        }

        } catch (\Exception $e)
        {

        }

        $episodeRepo = $this->entityManager->getRepository(Episode::class);
        /** @var User $user */
        $user = $this->entityManager->getReference(User::class, 1);
        $checkShow2 = $episodeRepo->getUnwatchedEpisodes($user);

        return ['result' => $result, 'resultCount' => $resultCount, 'upcomingEpisode' => $upcomingEpisode];
    }

    /**
     * @param int $showID
     * @return bool|array
     * @throws ORMException
     */
    private function checkShowForUnwatchedEpisodes($showID)
    {
        /** @var EpisodeRepository $episodeRepo */
        $episodeRepo = $this->entityManager->getRepository(Episode::class);

        /** @var User $user */
        $user = $this->entityManager->getReference(User::class, 1);
        $unwatchedEpisodes = $episodeRepo->getUnwatchedEpisodesDeprecated($showID, $user);
        if (count($unwatchedEpisodes) > 0) {
            return $unwatchedEpisodes;
        } else {
            return false;
        }
    }
}
