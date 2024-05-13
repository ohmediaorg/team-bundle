<?php

namespace OHMedia\TeamBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OHMedia\SecurityBundle\Entity\Traits\BlameableTrait;
use OHMedia\TeamBundle\Repository\TeamMemberRepository;

#[ORM\Entity(repositoryClass: TeamMemberRepository::class)]
class TeamMember
{
    use BlameableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $ordinal = 9999;

    #[ORM\ManyToOne(inversedBy: 'members')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Team $team = null;

    public function __toString(): string
    {
        return $this->header;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrdinal(): ?int
    {
        return $this->ordinal;
    }

    public function setOrdinal(int $ordinal): self
    {
        $this->ordinal = $ordinal;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): static
    {
        $this->team = $team;

        return $this;
    }
}
