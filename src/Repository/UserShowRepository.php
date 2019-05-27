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

    /**
     * @param User $user
     * @param int $status
     * @return mixed
     */
    public function getShows(User $user, $status = 0)
    {
        return $this->createQueryBuilder('us')
            ->select('us')
            ->innerJoin('us.show', 's')
            ->innerJoin('s.episodes', 'e')
            ->leftJoin('us.userEpisodes', 'ue', Join::WITH, 'us.show = ue.userShow AND ue.user = :user AND ue.episode = e AND ue.status = :watched')
            ->where('us.user = :user')
            ->andWhere('us.status = :status')
            ->setParameters([
                'watched' => UserEpisode::STATUS_WATCHED,
                'user' => $user,
                'status' => $status,
            ])
            ->groupBy('us')
            ->orderBy('s.status', 'desc')
            ->addOrderBy('s.name', 'asc')
            ->getQuery()
            ->getResult()
            ;
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
        return $this->createQueryBuilder('us')
            ->select('s.id, s.name, s.summary, s.status, us.id as userShowId, us.offset')
            ->addSelect('SUM(CASE WHEN ue.status = 1 THEN 1 ELSE 0 END) as watched')
            ->innerJoin('us.show', 's')
            ->innerJoin('s.episodes', 'e')
            ->leftJoin(UserEpisode::class, 'ue', Join::WITH, 'ue.user = :user AND ue.episode = e')
            ->where('us.user = :user')
            ->andWhere('us.id = :showId')
            ->setParameters(['user' => $user, 'showId' => $showId])
            ->groupBy('s')
            ->getQuery()
            ->getSingleResult();
    }
}
