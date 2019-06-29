<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserEpisode;
use App\Entity\UserShow;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;

class UserEpisodeRepository extends EntityRepository
{
    const DATE_ADD = "date_add(%s, CASE WHEN us.offset IS NOT NULL AND us.offset != 0 THEN us.offset ELSE u.defaultOffset END, 'hour')";

    public function watchAll(UserShow $userShow)
    {
        $this->createQueryBuilder('ue')
            ->update(UserEpisode::class, 'ue')
            ->set('ue.status', UserEpisode::STATUS_WATCHED)
            ->where('ue.userShow = :userShow')
            ->setParameter('userShow', $userShow)
            ->getQuery()
            ->execute()
        ;
    }

    public function getUpcomingUpdatedEpisodes(UserShow $userShow)
    {
        return $this->createQueryBuilder('ue')
            ->select('ue')
            ->innerJoin('ue.episode', 'e')
            ->innerJoin('ue.userShow', 'us')
            ->innerJoin('ue.user', 'u')
            ->where('ue.userShow = :userShow')
            ->andWhere(sprintf(self::DATE_ADD, 'e.airstamp') . ' > :now')
            ->setParameters(['userShow' => $userShow, 'now' => new DateTime()])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function getWatchedDuration(User $user)
    {
        return $this->createQueryBuilder('ue')
            ->select('SUM(e.duration)')
            ->innerJoin('ue.episode', 'e')
            ->where('ue.user = :user')
            ->andWhere('ue.status = :watched')
            ->setParameters(['watched' => UserEpisode::STATUS_WATCHED, 'user' => $user])
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    public function getLastEpisodes(User $user, $limit)
    {
        return $this->createQueryBuilder('ue')
            ->select('ue.created, e.season, e.episode, e.airstamp, e.name, e.duration, s.name as showName')
            ->addSelect(sprintf(EpisodeRepository::DATE_ADD, 'e.airstamp', 'userAirstamp'))
            ->innerJoin('ue.episode', 'e')
            ->innerJoin('e.show', 's')
            ->innerJoin(UserShow::class, 'us', Join::WITH, 'us.show = e.show AND us.user = :user')
            ->innerJoin(User::class, 'u', Join::WITH, 'u = :user')
            ->where('ue.user = :user')
            ->andWhere('ue.status = :statusWatched')
            ->orderBy('ue.created', 'desc')
            ->groupBy('ue')
            ->setParameters(['user' => $user, 'statusWatched' => UserEpisode::STATUS_WATCHED])
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
            ;
    }
}
