<?php

namespace App\Repository;

use App\Entity\Episode;
use App\Entity\Show;
use App\Entity\User;
use App\Entity\UserEpisode;
use App\Entity\UserShow;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

class UserEpisodeRepository extends EntityRepository
{
    public function watchAll(User $user, UserShow $userShow)
    {
        $this->createQueryBuilder('ue')
            ->update(UserEpisode::class, 'ue')
            ->set('ue.status', UserEpisode::STATUS_WATCHED)
            ->where('ue.user = :user')
            ->andWhere('ue.userShow = :userShow')
            ->setParameters([
                'user' => $user,
                'userShow' => $userShow,
            ])
            ->getQuery()
            ->execute();
    }

    public function getWatchedDuration(User $user)
    {
        return $this->createQueryBuilder('ue')
            ->select('SUM(e.duration)')
            ->innerJoin('ue.episode', 'e')
            ->where('ue.user = :user')
            ->andWhere('ue.status = :watched')
            ->setParameters(['watched' => UserEpisode::STATUS_WATCHED, 'user' => $user])
            ->getQuery()
            ->getSingleScalarResult();
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
            ->orderBy('ue.created', 'desc')
            ->setParameter('user', $user)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $showID
     * @param $user
     * @return array
     * @deprecated
     */
    public function getEpisodes($showID, $user)
    {
        /** @var User $user */
        $userID = $user->getId();
        $watched = [];
        $result = $this->createQueryBuilder('p')
            ->andWhere('p.userID = :userID')
            ->setParameter('userID', $userID)
            ->andWhere('p.showID = :showID')
            ->setParameter('showID', $showID)
            ->select('p.episodeID, p.comment, p.status')
            ->getQuery()
            ->getResult();

        foreach ($result as $episode) {
            $watched[$episode['episodeID']] = [
                    'comment' => $episode['comment'],
                    'status' => $episode['status']
                ];
        }
        return $watched;
    }

    /**
     * @param $showID
     * @param $userID
     * @return array
     * @deprecated
     */
    public function getWatchedEpisodes($showID, $userID)
    {
        $watched = [];
        $result = $this->createQueryBuilder('p')
            ->andWhere('p.userID = :userID')
            ->setParameter('userID', $userID)
            ->andWhere('p.showID = :showID')
            ->setParameter('showID', $showID)
            ->andWhere('p.status = :status')
                ->setParameter('status', UserEpisode::STATUS_WATCHED)
            ->select('p.episodeID')
            ->getQuery()
            ->getResult();

        foreach ($result as $episode) {
            $watched[] = $episode['episodeID'];
        }
        return $watched;
    }

    /**
     * @param int $showID
     * @param int $userID
     * @return int
     * @deprecated
     */
    public function countWatchedEpisodes($showID, $userID)
    {
        $qb = $this->createQueryBuilder('p')
            ->select('count(p.episodeID)')
            ->andWhere('p.userID = :userID')
            ->setParameter('userID', $userID)
            ->andWhere('p.showID = :showID')
            ->setParameter('showID', $showID)
            ->andWhere('p.status = :status')
            ->setParameter('status', UserEpisode::STATUS_WATCHED)
            ->getQuery();

        try {
            return $qb->getSingleScalarResult();
        } catch (\Exception $e) {
            error_log(__METHOD__ .' fails: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * @param $userID
     * @return array
     * @deprecated
     */
    public function getAllWatchedEpisodes($userID)
    {
        $watched = [];
        $result = $this->createQueryBuilder('p')
            ->andWhere('p.userID = :userID')
            ->setParameter('userID', $userID)
            ->andWhere('p.status = :status')
                ->setParameter('status', UserEpisode::STATUS_WATCHED)
            ->select('p.episodeID')
            ->getQuery()
            ->getResult();

        foreach ($result as $episode) {
            $watched[] = $episode['episodeID'];
        }
        return $watched;
    }

    /**
     * @param string $dateFrom
     * @param string $dateTo
     * @param User $user
     * @return array
     * @deprecated
     */
    public function getWatchedEpisodesInRange($dateFrom, $dateTo, $user)
    {
        /** @var User $user */
        if ($user) {
            $userID = $user->getId();
        } else {
            $userID=0;
        }

        $result = $this->createQueryBuilder('p')
            ->select('p.episodeID')
            ->innerJoin(UserShow::class, 'us', Join::WITH, 'us.showID = p.showID AND us.userID = :userID')
            ->innerJoin(User::class, 'u', Join::WITH, 'u.id = :userID')
            ->andWhere('p.status = :status')
            ->andWhere("p.airdate >= DATE_ADD(:dateFrom, CASE WHEN us.offset IS NOT NULL THEN us.offset * -1 ELSE u.defaultOffset * -1 END, 'hour')")
            ->andWhere("p.airdate <= DATE_ADD(:dateTo, CASE WHEN us.offset IS NOT NULL THEN us.offset * -1 ELSE u.defaultOffset * -1 END, 'hour')")
            ->andWhere('p.userID = :userID')
            ->setParameters([
                'userID' => $userID,
                'status' => UserEpisode::STATUS_WATCHED,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo
            ])
            ->getQuery()
            ->getResult();

        return array_column($result, 'episodeID');
    }
}
