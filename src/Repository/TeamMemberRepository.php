<?php

namespace OHMedia\TeamBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use OHMedia\TeamBundle\Entity\TeamMember;
use OHMedia\WysiwygBundle\Repository\WysiwygRepositoryInterface;

/**
 * @method TeamMember|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeamMember|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeamMember[]    findAll()
 * @method TeamMember[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamMemberRepository extends ServiceEntityRepository implements WysiwygRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamMember::class);
    }

    public function save(TeamMember $teamMember, bool $flush = false): void
    {
        $this->getEntityManager()->persist($teamMember);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TeamMember $teamMember, bool $flush = false): void
    {
        $this->getEntityManager()->remove($teamMember);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function containsWysiwygShortcodes(string ...$shortcodes): bool
    {
        $ors = [];
        $params = new ArrayCollection();

        foreach ($shortcodes as $i => $shortcode) {
            $ors[] = 'tm.bio LIKE :shortcode_'.$i;
            $params[] = new Parameter('shortcode_'.$i, '%'.$shortcode.'%');
        }

        return $this->createQueryBuilder('tm')
            ->select('COUNT(tm)')
            ->where(implode(' OR ', $ors))
            ->setParameters($params)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }
}
