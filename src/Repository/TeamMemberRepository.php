<?php

namespace OHMedia\TeamBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    public function getShortcodeQueryBuilder(string $shortcode): QueryBuilder
    {
        return $this->createQueryBuilder('tm')
            ->where('tm.bio LIKE :shortcode')
            ->setParameter('shortcode', '%'.$shortcode.'%');
    }

    public function getShortcodeRoute(): string
    {
        return 'team_member_edit';
    }

    public function getShortcodeRouteParams(mixed $entity): array
    {
        return ['id' => $entity->getId()];
    }

    public function getShortcodeHeading(): string
    {
        return 'Teams';
    }

    public function getShortcodeLinkText(mixed $entity): string
    {
        return sprintf(
            '%s - Team Member (ID:%s)',
            (string) $entity->getTeam(),
            $entity->getId(),
        );
    }
}
