<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserEpisode;
use App\Entity\UserShow;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\Security\Core\User\UserInterface;

class UserEpisodeRepository extends EntityRepository
{
    public function watchAll(UserShow $userShow): void
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

    public function getUpcomingUpdatedEpisodes(UserShow $userShow): ?array
    {
        return $this->createQueryBuilder('ue')
            ->select('ue')
            ->innerJoin('ue.episode', 'e')
            ->innerJoin('ue.userShow', 'us')
            ->innerJoin('ue.user', 'u')
            ->where('ue.userShow = :userShow')
            ->andWhere(sprintf(EpisodeRepository::DATE_ADD, 'e.airstamp') . ' > :now')
            ->setParameters(['userShow' => $userShow, 'now' => new DateTime()])
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getWatchedDuration(UserInterface $user): ?int
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

    public function getLastEpisodes(UserInterface $user, $limit): ?array
    {
        return $this->createQueryBuilder('ue')
            ->select('ue.id, ue.created, ue.updated, e.season, e.episode, e.airstamp, e.name, e.duration')
            ->addSelect('s.name as showName')
            ->addSelect(sprintf(EpisodeRepository::DATE_ADD, 'e.airstamp') . ' as userAirstamp')
            ->innerJoin('ue.episode', 'e')
            ->innerJoin('e.show', 's')
            ->innerJoin(
                UserShow::class,
                'us',
                Join::WITH,
                'us.show = e.show AND us.user = :user'
            )
            ->innerJoin(User::class, 'u', Join::WITH, 'u = :user')
            ->where('ue.user = :user')
            ->andWhere('ue.status = :statusWatched')
            ->orderBy('ue.created', 'desc')
            ->groupBy('ue.id')
            ->setParameters(['user' => $user, 'statusWatched' => UserEpisode::STATUS_WATCHED])
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
            ;
    }
}
