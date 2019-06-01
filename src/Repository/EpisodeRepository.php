<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserEpisode;
use App\Entity\UserShow;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;

class EpisodeRepository extends EntityRepository
{
    const DATE_ADD = "date_add(%s, CASE WHEN us.offset IS NOT NULL AND us.offset != 0 THEN us.offset ELSE u.defaultOffset END, 'hour') as %s";
    const DATE_SUB = "DATE_SUB(%s, CASE WHEN us.offset IS NOT NULL AND us.offset != 0 THEN us.offset ELSE u.defaultOffset END, 'hour')";

    /**
     * @param User $user
     * @param int $status
     *
     * @return array
     */
    public function getShowsWithUnwatchedEpisodes(User $user, $status = 0): ?array
    {
        return $this->createQueryBuilder('e')
            ->select('s.id, s.name, count(e.id) as episodes, us.id as userShowId')
            ->innerJoin('e.show', 's')
            ->innerJoin(UserShow::class, 'us', Join::WITH, 'us.show = e.show AND us.user = :user')
            ->innerJoin(User::class, 'u', Join::WITH, 'u = :user')
            ->leftJoin(UserEpisode::class, 'ue', Join::WITH, 'ue.user = :user AND ue.episode = e AND ue.userShow = us')
            ->andWhere('e.airstamp < ' . sprintf(self::DATE_SUB, ':dateTo'))
            ->andWhere('ue.status != :watched OR ue.status IS NULL')
            ->andWhere('us.status = :showStatus')
            ->setParameters([
                'watched' => UserEpisode::STATUS_WATCHED,
                'dateTo' => date('Y-m-d H:i'),
                'user' => $user,
                'showStatus' => $status,
            ])
            ->groupBy('us')
            ->orderBy('s.name', 'asc')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     * @param int $showId
     * @param string $order
     * @return array
     */
    public function getUnwatchedEpisodes(User $user, int $showId, string $order = 'asc'): ?array
    {
        return $this->createQueryBuilder('e')
            ->select('e.id, e.season, e.episode, e.airstamp, e.duration, e.name, e.summary, ue.comment, ue.status, us.id as userShowId')
            ->addSelect(sprintf(self::DATE_ADD, 'e.airstamp', 'userAirstamp'))
            ->innerJoin('e.show', 's')
            ->innerJoin(UserShow::class, 'us', Join::WITH, 'us.show = s AND us.user = :user')
            ->innerJoin(User::class, 'u', Join::WITH, 'u = :user')
            ->leftJoin(UserEpisode::class, 'ue', Join::WITH, 'ue.user = :user AND ue.episode = e AND ue.userShow = us')
            ->andWhere('e.airstamp < ' . sprintf(self::DATE_SUB, ':dateTo'))
            ->andWhere('ue.status != :watched OR ue.status IS NULL')
            ->andWhere('us.id = :userShowId')
            ->setParameters([
                'watched' => UserEpisode::STATUS_WATCHED,
                'dateTo' => date("Y-m-d H:i"),
                'user' => $user,
                'userShowId' => $showId,
            ])
            ->orderBy('e.airstamp', $order)
            ->addOrderBy('e.season', $order)
            ->addOrderBy('e.episode', $order)
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     * @param int  $showId
     * @return array
     * @throws NonUniqueResultException
     */
    public function getNextEpisode(User $user, int $showId): ?array
    {
        return $this->createQueryBuilder('e')
            ->select('e.id, e.season, e.episode, e.airstamp, e.duration, e.name, e.summary, ue.comment, ue.status, us.id as userShowId')
            ->addSelect(sprintf(self::DATE_ADD, 'e.airstamp', 'userAirstamp'))
            ->innerJoin('e.show', 's')
            ->innerJoin(UserShow::class, 'us', Join::WITH, 'us.show = s AND us.user = :user')
            ->innerJoin(User::class, 'u', Join::WITH, 'u = :user')
            ->leftJoin(UserEpisode::class, 'ue', Join::WITH, 'ue.user = :user AND ue.episode = e AND ue.userShow = us')
            ->where('s.id = :showId')
            ->andWhere('e.airstamp > ' . sprintf(self::DATE_SUB, ':dateFrom'))
            ->setParameters([
                'user' => $user,
                'dateFrom' => date("Y-m-d H:i"),
                'showId' => $showId,
            ])
            ->orderBy('e.airstamp', 'asc')
            ->addOrderBy('e.season', 'asc')
            ->addOrderBy('e.episode', 'asc')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param DateTime $dateFrom
     * @param DateTime $dateTo
     * @param User|null $user
     * @return array
     */
    public function getEpisodes(DateTime $dateFrom, DateTime $dateTo, User $user = null)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.id, e.duration')
            ->addSelect(sprintf(self::DATE_ADD, 'e.airstamp', 'userAirstamp'))
            ->addSelect('e.airstamp')
            ->addSelect('s.name as showName, s.id as showId, us.status as showStatus')
            ->addSelect('e.name, e.season, e.episode')
            ->addSelect('u.defaultOffset, us.offset, us.id as userShowId')
            ->addSelect('ue.status as episodeStatus')
            ->innerJoin(UserShow::class, 'us', Join::WITH, 'us.show = e.show AND us.user = :user')
            ->innerJoin(User::class, 'u', Join::WITH, 'u = :user')
            ->innerJoin('e.show', 's')
            ->leftJoin(UserEpisode::class, 'ue', Join::WITH, 'ue.user = :user AND ue.episode = e')
            ->andWhere('e.airstamp >= ' . sprintf(self::DATE_SUB, ':dateFrom'))
            ->andWhere('e.airstamp <= ' . sprintf(self::DATE_SUB, ':dateTo'))
            ->setParameters([
                'user' => $user,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
            ])
            ->orderBy('userAirstamp', 'ASC')
            ->addOrderBy('e.season', 'ASC')
            ->addOrderBy('e.episode', 'ASC');


        $status[] = 0;
        if ($user->getCalendarShow())
        {
            $status = array_merge($status, $user->getCalendarShow());
        }
        $qb->andWhere('us.status IN (:status)')
            ->setParameter('status', $status);

        return $qb->getQuery()
            ->getResult();
    }

    public function getUserShowEpisodes(User $user, UserShow $userShow, $limit = 100)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.id, e.season, e.episode, e.airstamp, e.name, e.summary, e.duration')
            ->addSelect(sprintf(self::DATE_ADD, 'e.airstamp', 'userAirstamp'))
            ->addSelect('ue.comment, ue.status, us.id as userShowId')
            ->innerJoin(UserShow::class, 'us', Join::WITH, 'us.show = e.show AND us.user = :user')
            ->innerJoin('us.user', 'u')
            ->innerJoin('e.show', 's')
            ->leftJoin(UserEpisode::class, 'ue', Join::WITH, 'ue.user = :user AND ue.episode = e AND ue.userShow = :userShow')
            ->where('u = :user')
            ->andWhere('e.show = :show')
            ->andWhere('us = :userShow')
            ->setParameters([
                'user' => $user,
                'show' => $userShow->getShow(),
                'userShow' => $userShow
            ])
            ->groupBy('e')
            ->orderBy('e.airstamp', 'desc');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()
            ->getResult();
    }

    public function getUnwatchedEpisodeEntities(User $user, UserShow $userShow)
    {
        return $this->createQueryBuilder('e')
            ->select('e')
            ->leftJoin(UserEpisode::class, 'ue', Join::WITH, 'ue.user = :user AND ue.episode = e AND ue.userShow = :userShow')
            ->where('e.show = :show')
            ->andWhere('ue.id IS NULL')
            ->andWhere('e.airstamp < :now')
            ->setParameters(['user' => $user, 'userShow' => $userShow, 'show' => $userShow->getShow(), 'now' => new DateTime()])
            ->getQuery()
            ->getResult();
    }
}
