<?php

namespace App\Service;

use App\Entity\Episode;
use App\Entity\Show;
use App\Entity\UserShow;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

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

    public function updateShow($id, $data = [])
    {
        $show = $this->entityManager->getReference(Show::class, $id);
        $userShow = $this->entityManager
            ->getRepository(UserShow::class)
            ->findOneBy(['show' => $show, 'user' => $this->user]);

        if (!$userShow) {
            $userShow = (new UserShow())->setUser($this->user)->setShow($show);
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
                $time = $episode->getAirtime() ?: '00:00';
                $date = (new DateTime())->createFromFormat('Y-m-d H:i', $episode->getAirdate() . ' ' . $time);

                /** @var Episode $episode */
                if ($date < $now) {
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
                'episodesCount' => $userShow->getShow()->getEpisodes()->count(),
                'watchedCount' => $userShow->getUserEpisodes()->count(),
                'lastEpisode' => $lastEpisode,
                'nextEpisode' => $nextEpisode,
                'offset' => $userShow->getOffset(),
            ];
        }

        return $formatted;
    }
}
