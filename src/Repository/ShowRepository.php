<?php

namespace App\Repository;

use App\Entity\UserShow;
use Doctrine\ORM\EntityRepository;

class ShowRepository extends EntityRepository
{
    /**
     * @param string $name
     * @param bool $full
     * @return array
     */
    public function findAllByName($name, $full = false)
    {
        $names = str_replace('"', " ", $name);
        $names = str_replace('%20', " ", $names);
        $names = preg_replace("/\s+/", " ", $names);
        $names = explode(" ", $names);

        $qb = $this->createQueryBuilder('s')
            ->leftJoin(UserShow::class, 'us', 'WITH', 'us.show = s')
            ->where('s.name LIKE :word')
            ->setParameter('word', '%' . $names[0] . '%')
            ->orderBy('us.id', 'desc')
            ->addOrderBy('s.rating', 'desc')
            ->addOrderBy('s.weight', 'desc')
            ->groupBy('s.id')
        ;

        unset($names[0]);
        foreach ($names as $key => $name) {
            $qb->andWhere('s.name LIKE :word' . $key)
                ->setParameter('word' . $key, '%' . $name . '%')
            ;
        }

        if (!$full) {
            $qb->select('s.id, s.name')
                ->setMaxResults(10)
            ;
        } else {
            $qb->select('s');
        }

        $result = $qb->getQuery()
            ->getResult()
        ;

        return $full ? $result : array_column($result, 'name');
    }

    /**
     * @param     $userShows
     * @param int $userID
     * @return array
     */
    public function getUserShows($userShows, $userID = 0)
    {
        return $this->createQueryBuilder('p')
            ->select('p.showID, p.status, p.name, us.offset')
            ->where('p.showID IN (:userShows)')
            ->setParameter('userShows', array_values($userShows))
            ->leftJoin("AppBundle:UserShows", 'us', 'WITH', 'us.showID = p.showID AND us.userID=:userID')
            ->setParameter('userID', $userID)
            ->orderBy('p.status', 'desc')->addOrderBy('p.name', 'asc')
            ->getQuery()
            ->getResult()
            ;
    }
}
