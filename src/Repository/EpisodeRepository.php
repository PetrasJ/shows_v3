<?php

namespace App\Repository;

use App\Entity\Show;
use App\Entity\User;
use App\Entity\UserEpisode;
use App\Entity\UserShow;
use DateTime;
use Doctrine\ORM\EntityRepository;
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
            ->select('s.id, s.name, count(e.id) as episodes')
            ->innerJoin('e.show', 's')
            ->innerJoin(UserShow::class, 'us', Join::WITH, 'us.show = e.show AND us.user = :user')
            ->innerJoin(User::class, 'u', Join::WITH, 'u = :user')
            ->leftJoin(UserEpisode::class, 'ue', Join::WITH, 'ue.user = :user AND ue.episode = e')
            ->andWhere('e.airstamp < ' . sprintf(self::DATE_SUB, ':dateTo'))
            ->andWhere('ue.status != :watched OR ue.status IS NULL')
            ->andWhere('us.status = :showStatus')
            ->setParameters([
                'watched' => UserEpisode::STATUS_WATCHED,
                'dateTo' => date("Y-m-d H:i"),
                'user' => $user,
                'showStatus' => $status,
            ])
            ->groupBy('s')
            ->orderBy('e.airdate', 'DESC')
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
            ->select('e.id, e.season, e.episode, e.airstamp, e.duration, e.name, e.summary, ue.comment, ue.status')
            ->addSelect(sprintf(self::DATE_ADD, 'e.airstamp', 'userAirstamp'))
            ->innerJoin('e.show', 's')
            ->innerJoin(UserShow::class, 'us', Join::WITH, 'us.show = e.show AND us.user = :user')
            ->innerJoin(User::class, 'u', Join::WITH, 'u = :user')
            ->leftJoin(UserEpisode::class, 'ue', Join::WITH, 'ue.user = :user AND ue.episode = e')
            ->andWhere('e.airstamp < ' . sprintf(self::DATE_SUB, ':dateTo'))
            ->andWhere('ue.status != :watched OR ue.status IS NULL')
            ->andWhere('s.id = :showId')
            ->setParameters([
                'watched' => UserEpisode::STATUS_WATCHED,
                'dateTo' => date("Y-m-d H:i"),
                'user' => $user,
                'showId' => $showId,
            ])
            ->orderBy('e.airstamp', $order)
            ->addOrderBy('e.season', $order)
            ->addOrderBy('e.episode', $order)
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $dateFrom
     * @param string $dateTo
     * @return array
     * @deprecated
     */
    private function getAllUsersEpisodes($dateFrom, $dateTo): ?array
    {
        $ignoredShows = [253, 1850, 340, 6514, 13615];

        return $this->createQueryBuilder('e')
            ->select("e.episodeID, concat(e.airdate,' ',e.airtime) AS airdate, e.duration")
            ->addSelect("concat(e.airdate,' ',e.airtime) AS original_airdatetime, e.name")
            ->addSelect('s.name AS showName, s.showID, e.season, e.episode')
            ->innerJoin(Show::class, 's', Join::WITH, 's.showID = e.showID')
            ->innerJoin(UserShow::class, 'us', Join::WITH, 'us.showID = e.showID')
            ->andWhere("e.airdate >= :dateFrom")
            ->andWhere("e.airdate <= :dateTo")
            ->andWhere('s.showID NOT IN (:ignoredShows)')
            ->setParameters([
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'ignoredShows' => $ignoredShows,
            ])
            ->groupBy('e.episodeID')
            ->getQuery()
            ->getResult();
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
            ->addSelect('u.defaultOffset, us.offset')
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

    public function getUserShowEpisodes(User $user, Show $show, $limit = 100)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.id, e.season, e.episode, e.airstamp, e.name, e.summary, e.duration')
            ->addSelect(sprintf(self::DATE_ADD, 'e.airstamp', 'userAirstamp'))
            ->addSelect('ue.comment, ue.status')
            ->innerJoin(UserShow::class, 'us', Join::WITH, 'us.show = e.show AND us.user = :user')
            ->innerJoin('us.user', 'u')
            ->innerJoin('e.show', 's')
            ->leftJoin(UserEpisode::class, 'ue', Join::WITH, 'ue.user = :user AND ue.episode = e')
            ->where('u = :user')
            ->andWhere('e.show = :show')
            ->setParameters([
                'user' => $user,
                'show' => $show
            ])
            ->orderBy('e.airstamp', 'desc');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * @param int $showID
     * @param User $user
     * @return array
     * @deprecated
     */
    public function getAllShowEpisodes(int $showID, User $user)
    {
        $userID = $user->getId();

        return $this->createQueryBuilder('e')
            ->select("e.episodeID, e.duration")
            ->addSelect(sprintf(self::DATE_ADD, "concat(e.airdate, ' ', e.airtime)"))
            ->addSelect("concat(e.airdate,' ',e.airtime) as original_airdatetime")
            ->addSelect('e.season, e.episode, e.summary, e.name, s.name as showName')
            ->innerJoin(Show::class, 's', Join::WITH, 's.showID = :showID')
            ->innerJoin(User::class, 'u', Join::WITH, 'u.id = :userID')
            ->innerJoin(UserShow::class, 'us', Join::WITH, 'us.showID = e.showID AND us.userID = :userID')
            ->where('e.showID = :showID')
            ->setParameters([
                'showID' => $showID,
                'userID' => $userID,
            ])
            ->groupBy('e.episodeID')
            ->orderBy('e.airdate', 'desc')
            ->addOrderBy('LENGTH(e.season)', 'desc')
            ->addOrderBy('e.season', 'desc')
            ->addOrderBy('LENGTH(e.episode)', 'desc')
            ->addOrderBy('e.episode', 'desc')
            ->setMaxResults(UserEpisode::MAX_RESULT)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $showID
     * @param User $user
     *
     * @return array
     * @deprecated
     */
    public function getShowPastEpisodes($showID, User $user)
    {
        return $this->createQueryBuilder('e')
            ->select('e.episodeID')
            ->innerJoin(UserShow::class, 'us', Join::WITH, 'us.showID = e.showID')
            ->innerJoin(User::class, 'u', Join::WITH, 'u.id = :userID')
            ->andWhere('e.showID = :showID')
            ->andWhere("concat(e.airdate, ' ', e.airtime) < " . sprintf(self::DATE_SUB, ':dateTo'))
            ->setParameters([
                'dateTo' => date("Y-m-d H:i"),
                'showID' => $showID,
                'userID' => $user->getId()
            ])
            ->groupBy('e.episodeID')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Show $show
     * @param User $user
     *
     * @return array
     *
     * @deprecated
     */
    public function getUnwatchedEpisodesDeprecated(Show $show, User $user)
    {
        return $this->createQueryBuilder('e')
            ->select('e.id, e.airdate')
            ->innerJoin(UserShow::class, 'us', Join::WITH, 'us.show = e.show AND us.user = :user')
            ->innerJoin(User::class, 'u', Join::WITH, 'u = :user')
            ->leftJoin(UserEpisode::class, 'ue', Join::WITH, 'ue.user = :user AND ue.episodeID = e.id')
            ->where('e.show = :show')
            ->andWhere("concat(e.airdate, ' ', e.airtime) < " . sprintf(self::DATE_SUB, ':dateTo'))
            ->andWhere('ue.status != :watched OR ue.status IS NULL')
            ->setParameters([
                'watched' => UserEpisode::STATUS_WATCHED,
                'dateTo' => date("Y-m-d H:i"),
                'show' => $show,
                'user' => $user
            ])
            ->orderBy('e.airdate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $showID
     * @param User $user
     *
     * @return int
     * @deprecated
     */
    public function countUnwatchedEpisodes(int $showID, User $user)
    {
        $today = date("Y-m-d H:i");

        $qb = $this->createQueryBuilder('e')
            ->select('count(e.episodeID)')
            ->innerJoin(UserShow::class, 'us', Join::WITH, 'us.showID = e.showID AND us.userID = :userID')
            ->innerJoin(User::class, 'u', Join::WITH, 'u.id = :userID')
            ->leftJoin(UserEpisode::class, 'ue', Join::WITH, 'ue.userID = :userID AND ue.episodeID = e.episodeID')
            ->where('e.showID = :showID')
            ->andWhere("concat(e.airdate, ' ', e.airtime) < " . sprintf(self::DATE_SUB, ':dateTo'))
            ->andWhere('ue.status != :watched OR ue.status IS NULL')
            ->setParameters([
                'watched' => UserEpisode::STATUS_WATCHED,
                'dateTo' => $today,
                'showID' => $showID,
                'userID' => $user->getId()
            ])
            ->orderBy('e.airdate', 'DESC')
            ->getQuery();

        try {
            return $qb->getSingleScalarResult();
        } catch (\Exception $e) {
            error_log(__METHOD__ . ' fails: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * @param User $user
     *
     * @return array
     * @deprecated
     */
    public function getUpcomingEpisodes(User $user)
    {
        $today = date("Y-m-d H:i");

        return $this->createQueryBuilder('e')
            ->select('e.episodeID, e.episode, e.season, e.name, s.name as showName, s.showID, e.duration')
            ->addSelect(sprintf(self::DATE_ADD, "concat(e.airdate, ' ', e.airtime)"))
            ->addSelect("concat(e.airdate,' ',e.airtime) as original_airdatetime")
            ->innerJoin(UserShow::class, 'us', Join::WITH, 'us.showID = e.showID AND us.userID = :userID')
            ->innerJoin(User::class, 'u', Join::WITH, 'u.id = :userID')
            ->innerJoin(Show::class, 's', Join::WITH, 's.showID = us.showID')
            ->where("concat(e.airdate, ' ',e.airtime) >= " . sprintf(self::DATE_SUB, ':today'))
            ->andWhere("concat(e.airdate, ' ',e.airtime) <= " . sprintf(self::DATE_SUB, ':tomorrow'))
            ->andWhere('us.status IS NULL')
            ->setParameters([
                'userID' => $user->getId(),
                'today' => $today,
                'tomorrow' => date("Y-m-d H:i", strtotime($today . " +1 day")),
            ])
            ->orderBy('e.airdate', 'ASC')
            ->getQuery()
            ->getResult();
    }

     /**
     * @param int $showID
     * @param int $offset
     * @return array
     * @deprecated
     */
    public function getNextAndPrevEpisodes($showID, $offset)
    {
        $today = date("Y-m-d H:i");
        try {
            $prev = $this->createQueryBuilder('e')
                ->select("e.showID, e.episodeID, e.name, e.season, e.episode, e.duration, e.airtime, e.summary, "
                    . "concat(e.airdate,' ',e.airtime) as original_airdatetime,
                    substring(DATE_ADD(concat(e.airdate,' ',e.airtime,':00'),:offset,'hour'),1,16) as airdate")
                ->where("substring(DATE_ADD(concat(e.airdate,' ',e.airtime,':00'),:offset, 'HOUR'),1,16) <= :today")
                ->setParameter('today', $today)
                ->setParameter('offset', $offset)
                ->andWhere('e.showID = :showID')
                ->setParameter('showID', $showID)
                ->orderBy('e.airdate', 'desc')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            $next = $this->createQueryBuilder('e')
                ->select("e.showID, e.episodeID, e.name, e.season, e.episode, e.duration, e.airtime, e.summary,
                    concat(e.airdate,' ',e.airtime) as original_airdatetime,
                    substring(DATE_ADD(concat(e.airdate,' ',e.airtime,':00'),:offset, 'HOUR'),1,16) as airdate")
                ->where("substring(DATE_ADD(concat(e.airdate,' ',e.airtime,':00'),:offset, 'HOUR'),1,16) > :today")
                ->setParameter('today', $today)
                ->setParameter('offset', $offset)
                ->andWhere('e.showID = :showID')
                ->setParameter('showID', $showID)
                ->orderBy('e.airdate')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            return ['prev' => $prev, 'next' => $next];
        } catch (\Exception $e) {
            error_log(__METHOD__ . ' fails: ' . $e->getMessage());
            return ['prev' => null, 'next' => null];
        }
    }

    /**
     * @param $episodes
     * @return mixed
     * @deprecated
     */
    public function countDuration($episodes)
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.episodeID IN (:episodes)')
            ->setParameter('episodes', array_values($episodes))
            ->select('SUM(e.duration) as duration')
            ->setMaxResults(1)
            ->getQuery();

        try {
            return $qb->getOneOrNullResult();
        } catch (\Exception $e) {
            error_log(__METHOD__ . ' fails: ' . $e->getMessage());
            return 0;
        }
    }

    public function getUnwatchedEpisodeEntities(User $user, Show $show)
    {
        return $this->createQueryBuilder('e')
            ->select('e')
            ->leftJoin(UserEpisode::class, 'ue', Join::WITH, 'ue.user = :user AND ue.episode = e')
            ->where('e.show = :show')
            ->andWhere('ue.id IS NULL')
            ->andWhere('e.airstamp < :now')
            ->setParameters(['user' => $user, 'show' => $show, 'now' => new DateTime()])
            ->getQuery()
            ->getResult();
    }
}
