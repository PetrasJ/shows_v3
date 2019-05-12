<?php

namespace App\Repository;

use App\Entity\UserShow;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class ShowRepository extends EntityRepository
{
    /**
     * @param $name
     * @return array
     */
    public function findAllByName($name)
    {
        $names = str_replace('"', " ", $name);
        $names = str_replace('%20', " ", $names);
        $names = preg_replace("/\s+/", " ", $names);
        $names = explode(" ", $names);

        $qb = $this->createQueryBuilder('p')
            ->leftJoin(UserShow::class, 'us', 'WITH', 'us.showID = p.showID')
            ->where('p.name LIKE :word')
            ->setParameter('word', '%' . $names[0] . '%')
            ->orderBy('us.showID', 'desc')
            ->addOrderBy('p.rating', 'desc')
            ->addOrderBy('p.weight', 'desc')
            ->groupBy('p.showID')
            ->setMaxResults(200);

        unset($names[0]);
        foreach ($names as $key => $name) {
            $qb->andWhere('p.name LIKE :word' . $key)
                ->setParameter('word' . $key, '%' . $name . '%');
        }

        return $qb->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * @param string $name
     * @return array
     */
    public function findAllByNameLimited($name)
    {
        $names = str_replace('"', " ", $name);
        $names = str_replace('%20', " ", $names);
        $names = preg_replace("/\s+/", " ", $names);
        $names = explode(" ", $names);

        $qb = $this->createQueryBuilder('p')
            ->select('p.name')
            ->leftJoin(UserShow::class, 'us', 'WITH', 'us.showID = p.showID')
            ->where('p.name LIKE :word')
            ->setParameter('word', '%' . $names[0] . '%')
            ->orderBy('us.showID', 'desc')
            ->addOrderBy('p.rating', 'desc')
            ->addOrderBy('p.weight', 'desc')
            ->groupBy('p.showID')
            ->setMaxResults(10);

        unset($names[0]);
        foreach ($names as $key => $name) {
            $qb->andWhere('p.name LIKE :word' . $key)
                ->setParameter('word' . $key, '%' . $name . '%');
        }

        $result = $qb->getQuery()
            ->getResult();

        return array_column($result, 'name');
    }

    /**
     * @param $userShows
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
            ->getResult();
    }
}
