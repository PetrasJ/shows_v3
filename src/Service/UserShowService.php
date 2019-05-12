<?php

namespace App\Service;

use App\Entity\Episode;
use App\Entity\UserShow;
use DateTime;
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
        $shows = $this->entityManager
            ->getRepository(UserShow::class)
            ->getShows($this->user, $status)
        ;

        return $this->formatShows($shows);
    }

    private function formatShows($shows)
    {
        $formatted = [];
        foreach ($shows as $userShow) {
            /** @var UserShow $userShow */
            $episodes = $userShow->getShow()->getEpisodes();

            $lastEpisode = null;
            $nextEpisode = null;
            $now = (new DateTime())->modify(sprintf('-%d hours', $userShow->getOffset()));
            foreach ($episodes as $episode) {
                $time = $episode->getAirtime() ? $episode->getAirtime() . ':00' : '00:00:00';
                $date = (new DateTime())->createFromFormat('Y-m-d h:i:s', $episode->getAirdate() . ' ' . $time);

                if ($date < $now) {
                    $lastEpisode = $episode;
                } else {
                    $nextEpisode = $episode;
                    break;
                }
            }

            $formatted[] = [
                'name' => $userShow->getShow()->getName(),
                'episodesCount' => $userShow->getShow()->getEpisodes()->count(),
                'watchedCount' => $userShow->getUserEpisodes()->count(),
                'lastEpisode' => $lastEpisode,
                'nextEpisode' => $nextEpisode,
            ];
        }

        return $formatted;
    }
}
