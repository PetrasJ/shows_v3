<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserEpisode;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Exception;

class UserShowRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function getAllUsersShows()
    {
        $result = $this->createQueryBuilder('p')
            ->select('s.id')
            ->innerJoin('p.show', 's')
            ->groupBy('s')
            ->getQuery()
            ->getResult()
        ;

        return array_column($result, 'id');
    }

    public function getShows(User $user, $status = 0)
    {
        return $this->createQueryBuilder('us')
            ->select('s.id, s.name, s.summary, s.status, us.id as userShowId, us.offset, us.status as userShowStatus')
            ->addSelect('SUM(CASE WHEN ue.status = 1 THEN 1 ELSE 0 END) as watched')
            ->innerJoin('us.show', 's')
            ->leftJoin('s.episodes', 'e')
            ->innerJoin('us.user', 'u')
            ->leftJoin(UserEpisode::class, 'ue', Join::WITH, 'ue.user = :user AND ue.episode = e AND us = ue.userShow')
            ->where('us.user = :user')
            ->andWhere('us.status = :status')
            ->setParameters(['user' => $user, 'status' => $status])
            ->groupBy('us')
            ->orderBy('s.status', 'desc')
            ->addOrderBy('s.name', 'asc')
            ->getQuery()
            ->getResult();
    }

    public function getEpisodes(User $user, $status)
    {
        return $this->createQueryBuilder('us')
            ->select('us.id as userShowId, e.id, e.season, e.episode, e.airstamp, e.name, e.duration')
            ->addSelect(sprintf(EpisodeRepository::DATE_ADD, 'e.airstamp', 'userAirstamp'))
            ->innerJoin('us.show', 's')
            ->innerJoin('s.episodes', 'e')
            ->innerJoin('us.user', 'u')
            ->leftJoin(UserEpisode::class, 'ue', Join::WITH, 'ue.user = :user AND ue.episode = e AND us = ue.userShow')
            ->where('us.user = :user')
            ->andWhere('us.status = :status')
            ->setParameters(['user' => $user, 'status' => $status])
            ->orderBy('e.airstamp', 'asc')
            ->addOrderBy('e.season', 'asc')
            ->addOrderBy('e.episode', 'asc')
            ->getQuery()
            ->getResult();
    }

    public function getUserShows(User $user, array $shows)
    {
        $userShows = $this->createQueryBuilder('us')
            ->select('s.id, us.status')
            ->innerJoin('us.show', 's')
            ->where('us.user = :user')
            ->andWhere('us.show IN (:shows)')
            ->setParameters(['user' => $user, 'shows' => $shows])
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($userShows as $userShow)
        {
            $result[$userShow['id']] = $userShow['status'];
        }

        return $result;
    }

    public function getUserShow(User $user, $showId)
    {
        try {
        return $this->createQueryBuilder('us')
            ->select('s.id, s.name, s.summary, s.status, us.id as userShowId, us.offset')
            ->addSelect('SUM(CASE WHEN ue.status = 1 THEN 1 ELSE 0 END) as watched')
            ->innerJoin('us.show', 's')
            ->leftJoin('s.episodes', 'e')
            ->leftJoin(UserEpisode::class, 'ue', Join::WITH, 'ue.user = :user AND ue.episode = e AND us = ue.userShow')
            ->where('us.user = :user')
            ->andWhere('us.id = :showId')
            ->setParameters(['user' => $user, 'showId' => $showId])
            ->groupBy('s')
            ->getQuery()
            ->getSingleResult();
        } catch (Exception $e) {
            return null;
        }
    }
}
