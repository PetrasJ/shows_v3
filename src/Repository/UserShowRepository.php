<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserEpisode;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;

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
            ->select('us.id as userShowId, e.id, e.season, e.episode, e.airstamp, e.name, e.duration, ue.status as userEpisodeStatus')
            ->addSelect(sprintf(EpisodeRepository::DATE_ADD, 'e.airstamp') . ' as userAirstamp')
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
            ->select('s.id, us.id as userShowId, us.status')
            ->addSelect('count(distinct e.id) as episodes, count(distinct ue.id) as watched')
            ->innerJoin('us.show', 's')
            ->leftJoin('us.userEpisodes', 'ue')
            ->leftJoin('s.episodes', 'e')
            ->where('us.user = :user')
            ->andWhere('ue.status = :watched')
            ->andWhere('us.show IN (:shows)')
            ->andWhere('e.airstamp < :now')
            ->setParameters([
                'user' => $user,
                'shows' => $shows,
                'watched' => UserEpisode::STATUS_WATCHED,
                'now' => new DateTime(),
            ])
            ->groupBy('us.id')
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($userShows as $userShow)
        {
            $result[$userShow['id']][] = [
                'userShowId' => $userShow['userShowId'],
                'status' => $userShow['status'],
                'watched' => $userShow['watched'],
                'unwatched' => $userShow['episodes'] - $userShow['watched'],
            ];
        }

        return $result;
    }

    /**
     * @param User $user
     * @param      $showId
     * @return mixed
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getUserShow(User $user, $showId)
    {
        return $this->createQueryBuilder('us')
            ->select('s.id, s.name, s.summary, s.status, us.id as userShowId, us.offset, us.status as userShowStatus')
            ->addSelect('SUM(CASE WHEN ue.status = 1 THEN 1 ELSE 0 END) as watched')
            ->addSelect('COUNT(e.id) as episodesCount')
            ->innerJoin('us.show', 's')
            ->innerJoin('us.user', 'u')
            ->leftJoin('s.episodes', 'e')
            ->leftJoin(UserEpisode::class, 'ue', Join::WITH, 'ue.user = :user AND ue.episode = e AND us = ue.userShow')
            ->where('us.user = :user')
            ->andWhere('e.airstamp < ' . sprintf(EpisodeRepository::DATE_SUB, ':now'))
            ->andWhere('us.id = :showId')
            ->setParameters(['user' => $user, 'showId' => $showId, 'now' => date('Y-m-d H:i')])
            ->groupBy('s')
            ->getQuery()
            ->getSingleResult();
    }
}
