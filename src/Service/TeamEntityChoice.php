<?php

namespace OHMedia\TeamBundle\Service;

use OHMedia\SecurityBundle\Service\EntityChoiceInterface;
use OHMedia\TeamBundle\Entity\Team;
use OHMedia\TeamBundle\Entity\TeamMember;

class TeamEntityChoice implements EntityChoiceInterface
{
    public function getLabel(): string
    {
        return 'Teams';
    }

    public function getEntities(): array
    {
        return [Team::class, TeamMember::class];
    }
}
