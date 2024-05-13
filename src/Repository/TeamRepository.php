<?php

namespace OHMedia\TeamBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use OHMedia\TeamBundle\Entity\Team;

/**
 * @method Team|null find($id, $lockMode = null, $lockVersion = null)
 * @method Team|null findOneBy(array $criteria, array $orderBy = null)
 * @method Team[]    findAll()
 * @method Team[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    public function save(Team $team, bool $flush = false): void
    {
        $this->getEntityManager()->persist($team);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Team $team, bool $flush = false): void
    {
        $this->getEntityManager()->remove($team);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
