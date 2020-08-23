<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserEpisode;
use App\Entity\UserShow;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\Security\Core\User\UserInterface;

class EpisodeRepository extends EntityRepository
{
    const DATE_ADD = "date_add(%s, CASE WHEN us.offset IS NOT NULL AND us.offset != 0 THEN us.offset ELSE u.defaultOffset END, 'hour')";
    const DATE_SUB = "date_sub(%s, CASE WHEN us.offset IS NOT NULL AND us.offset != 0 THEN us.offset ELSE u.defaultOffset END, 'hour')";

    public function getShowsWithUnwatchedEpisodes(UserInterface $user, int $status = 0): ?array
    {
        return $this->createQueryBuilder('e')
            ->select('s.id, s.name, count(e.id) as episodes, us.id as userShowId')
            ->innerJoin('e.show', 's')
            ->innerJoin(
                UserShow::class,
                'us',
                Join::WITH,
                'us.show = e.show AND us.user = :user'
            )
            ->innerJoin(User::class, 'u', Join::WITH, 'u = :user')
            ->leftJoin(
                UserEpisode::class,
                'ue',
                Join::WITH,
                'ue.user = :user AND ue.episode = e AND ue.userShow = us'
            )
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
            ->getResult()
            ;
    }

    public function getUnwatchedEpisodes(UserInterface $user, int $showId, string $order = 'asc'): ?array
    {
        return $this->createQueryBuilder('e')
            ->select('e.id, e.season, e.episode, e.airstamp, e.duration, e.name, e.summary, ue.comment')
            ->addSelect('ue.created, ue.updated, ue.status, us.id as userShowId')
            ->addSelect(sprintf(self::DATE_ADD, 'e.airstamp') . ' as userAirstamp')
            ->innerJoin('e.show', 's')
            ->innerJoin(
                UserShow::class,
                'us',
                Join::WITH,
                'us.show = s AND us.user = :user'
            )
            ->innerJoin(User::class, 'u', Join::WITH, 'u = :user')
            ->leftJoin(
                UserEpisode::class,
                'ue',
                Join::WITH,
                'ue.user = :user AND ue.episode = e AND ue.userShow = us'
            )
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
            ->setMaxResults(UserEpisode::MAX_RESULT)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param User $user
     * @param int $showId
     * @return array
     * @throws NonUniqueResultException
     */
    public function getNextEpisode(UserInterface $user, int $showId): ?array
    {
        return $this->createQueryBuilder('e')
            ->select('e.id, e.season, e.episode, e.airstamp, e.duration, e.name, e.summary, ue.comment')
            ->addSelect('ue.status, us.id as userShowId')
            ->addSelect(sprintf(self::DATE_ADD, 'e.airstamp') . ' as userAirstamp')
            ->innerJoin('e.show', 's')
            ->innerJoin(
                UserShow::class,
                'us',
                Join::WITH,
                'us.show = s AND us.user = :user'
            )
            ->innerJoin(User::class, 'u', Join::WITH, 'u = :user')
            ->leftJoin(
                UserEpisode::class,
                'ue',
                Join::WITH,
                'ue.user = :user AND ue.episode = e AND ue.userShow = us'
            )
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
            ->getOneOrNullResult()
            ;
    }

    public function getEpisodes(
        DateTime $dateFrom,
        DateTime $dateTo,
        UserInterface $user = null,
        bool $watching = false,
        bool $excludeWatched = false
    ): ?array {
        $qb = $this->createQueryBuilder('e')
            ->select('e.id, e.duration')
            ->addSelect(sprintf(self::DATE_ADD, 'e.airstamp') . ' as userAirstamp')
            ->addSelect('e.airstamp')
            ->addSelect('s.name as showName, s.id as showId, us.status as showStatus')
            ->addSelect('e.name, e.season, e.episode')
            ->addSelect('u.defaultOffset, us.offset, us.id as userShowId')
            ->addSelect('ue.status as episodeStatus')
            ->innerJoin(
                UserShow::class,
                'us',
                Join::WITH,
                'us.show = e.show AND us.user = :user'
            )
            ->innerJoin(User::class, 'u', Join::WITH, 'u = :user')
            ->innerJoin('e.show', 's')
            ->leftJoin(
                UserEpisode::class,
                'ue',
                Join::WITH,
                'ue.user = :user AND ue.episode = e'
            )
            ->andWhere('e.airstamp >= ' . sprintf(self::DATE_SUB, ':dateFrom'))
            ->andWhere('e.airstamp <= ' . sprintf(self::DATE_SUB, ':dateTo'))
            ->groupBy('e.id')
            ->setParameters([
                'user' => $user,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
            ])
            ->orderBy('userAirstamp', 'ASC')
            ->addOrderBy('e.season', 'ASC')
            ->addOrderBy('e.episode', 'ASC')
        ;

        if ($excludeWatched) {
            $qb->andWhere('ue.status IS NULL OR ue.status != :watched')
                ->setParameter('watched', UserEpisode::STATUS_WATCHED)
            ;
        }

        $status[] = 0;
        if ($user->getCalendarShow() && $watching === false) {
            $status = array_merge($status, $user->getCalendarShow());
        }
        $qb->andWhere('us.status IN (:status)')
            ->setParameter('status', $status)
        ;

        return $qb->getQuery()
            ->getResult()
            ;
    }

    public function getEpisodesPublic(DateTime $dateFrom, DateTime $dateTo): ?array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.id, e.duration')
            ->addSelect("e.airstamp, date_add(e.airstamp, 0, 'hour') as userAirstamp")
            ->addSelect('e.name, e.season, e.episode, s.name as showName')
            ->innerJoin(UserShow::class, 'us', Join::WITH, 'us.show = e.show')
            ->innerJoin('e.show', 's')
            ->andWhere('e.airstamp >=  :dateFrom')
            ->andWhere('e.airstamp <= :dateTo')
            ->setParameters([
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
            ])
            ->groupBy('e.id')
            ->orderBy('e.airstamp', 'ASC')
            ->addOrderBy('e.season', 'ASC')
            ->addOrderBy('e.episode', 'ASC')
        ;

        return $qb->getQuery()
            ->getResult()
            ;
    }

    public function getUserShowEpisodes(UserInterface $user, UserShow $userShow, $limit = UserEpisode::MAX_RESULT): ?array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.id, e.season, e.episode, e.airstamp, e.name, e.summary, e.duration')
            ->addSelect(sprintf(self::DATE_ADD, 'e.airstamp') . ' as userAirstamp')
            ->addSelect('ue.comment, ue.status, ue.created, ue.updated, us.id as userShowId')
            ->innerJoin(
                UserShow::class,
                'us',
                Join::WITH,
                'us.show = e.show AND us.user = :user'
            )
            ->innerJoin('us.user', 'u')
            ->innerJoin('e.show', 's')
            ->leftJoin(UserEpisode::class, 'ue', Join::WITH,
                'ue.user = :user AND ue.episode = e AND ue.userShow = :userShow')
            ->where('u = :user')
            ->andWhere('e.show = :show')
            ->andWhere('us = :userShow')
            ->setParameters([
                'user' => $user,
                'show' => $userShow->getShow(),
                'userShow' => $userShow,
            ])
            ->orderBy('e.airstamp', 'desc')
            ->addOrderBy('e.season', 'desc')
            ->addOrderBy('e.episode', 'desc')
        ;

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()
            ->getResult()
            ;
    }

    public function getUnwatchedEpisodeEntities(UserInterface $user, UserShow $userShow): ?array
    {
        return $this->createQueryBuilder('e')
            ->select('e')
            ->leftJoin(UserEpisode::class, 'ue', Join::WITH,
                'ue.user = :user AND ue.episode = e AND ue.userShow = :userShow')
            ->where('e.show = :show')
            ->andWhere('ue.id IS NULL')
            ->andWhere('e.airstamp < :now')
            ->setParameters([
                'user' => $user,
                'userShow' => $userShow,
                'show' => $userShow->getShow(),
                'now' => new DateTime(),
            ])
            ->getQuery()
            ->getResult()
            ;
    }
}
