<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserEpisode;
use App\Entity\UserShow;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Exception;

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
            ->execute()
        ;
    }

    public function getWatchedDuration(User $user)
    {
        try {
            return $this->createQueryBuilder('ue')
                ->select('SUM(e.duration)')
                ->innerJoin('ue.episode', 'e')
                ->where('ue.user = :user')
                ->andWhere('ue.status = :watched')
                ->setParameters(['watched' => UserEpisode::STATUS_WATCHED, 'user' => $user])
                ->getQuery()
                ->getSingleScalarResult()
                ;
        } catch (Exception $e) {
            return null;
        }
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
            ->groupBy('ue')
            ->setParameter('user', $user)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
            ;
    }
}
