<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserEpisode;
use App\Entity\UserShow;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr\Join;

class UserShowRepository extends EntityRepository
{
    public function getShows(User $user, $status = 0)
    {
        return $this->createQueryBuilder('us')
            ->select('us')
            ->innerJoin('us.show', 's')
            ->innerJoin('s.episodes', 'e')
            ->leftJoin('us.userEpisodes', 'ue', Join::WITH, 'ue.user = :user AND ue.episodeID = e.id AND ue.status = :watched')
            ->where('us.user = :user')
            ->andWhere('us.status = :status')
            ->setParameters([
                'watched' => UserEpisode::STATUS_WATCHED,
                'user' => $user,
                'status' => $status,
            ])
            ->groupBy('s')
            ->orderBy('s.status', 'desc')
            ->addOrderBy('s.name', 'asc')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param $userID
     * @param string $status
     * @return array
     * @throws ORMException
     * @deprecated
     */
    public function getShowsDeprecaated($userID, $status = UserShow::STATUS_WATCHING)
    {
        $user = $this->getEntityManager()->getReference(User::class, $userID);

        if ($status == UserShow::STATUS_WATCHING) {
            $ignore = [UserShow::STATUS_ARCHIVED, UserShow::STATUS_WATCH_LATER];
            $result = $this->createQueryBuilder('us')
                ->where('us.user = :user')
                ->setParameter('user', $user)
                ->andWhere('us.status is NULL')
                ->orWhere('us.status NOT IN (:status)')
                ->setParameter('status', array_values($ignore))
                ->getQuery()
                ->getResult()
            ;
        } elseif ($status == UserShow::STATUS_ARCHIVED) {
            $result = $this->createQueryBuilder('us')
                ->where('us.user = :user')
                ->setParameter('user', $user)
                ->andWhere('us.status = :status')
                ->setParameter('status', UserShow::STATUS_ARCHIVED)
                ->getQuery()
                ->getResult()
            ;
        } elseif ($status == UserShow::STATUS_WATCH_LATER) {
            $result = $this->createQueryBuilder('us')
                ->where('us.user = :user')
                ->setParameter('user', $user)
                ->andWhere('us.status = :status')
                ->setParameter('status', UserShow::STATUS_WATCH_LATER)
                ->getQuery()
                ->getResult()
            ;
        } else {
            $result = $this->createQueryBuilder('us')
                ->where('us.user = :user')
                ->setParameter('user', $user)
                ->getQuery()
                ->getResult()
            ;
        }

        return array_map(function ($userShow) {
            return $userShow->getShow();
        }, $result);
    }

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

    public function getUnwachedShows()
    {
        $user = $this->getEntityManager()->getReference(User::class, 1);
        $result = $this->createQueryBuilder('us')
            ->select('us, s')
            ->innerJoin('us.show', 's')
            ->where('us.userID = :userID')
            ->innerJoin('s.episodes', 'e')
            ->leftJoin(UserEpisode::class, 'ue', Join::WITH, 'ue.show = s AND ')
            ->andWhere('ue.status IS NULL')
            ->setParameter('userID', 1)
            ->getQuery()
            ->getResult()
        ;

        return $result;
    }
}
