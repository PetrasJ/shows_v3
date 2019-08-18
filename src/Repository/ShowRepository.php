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
    public function findAllByName($name, $full = false): ?array
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
}
