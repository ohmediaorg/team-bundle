<?php

namespace OHMedia\TeamBundle\Service;

use OHMedia\BackendBundle\Service\AbstractNavItemProvider;
use OHMedia\BootstrapBundle\Component\Nav\NavItemInterface;
use OHMedia\BootstrapBundle\Component\Nav\NavLink;
use OHMedia\TeamBundle\Entity\Team;
use OHMedia\TeamBundle\Security\Voter\TeamVoter;

class TeamNavItemProvider extends AbstractNavItemProvider
{
    public function getNavItem(): ?NavItemInterface
    {
        if ($this->isGranted(TeamVoter::INDEX, new Team())) {
            return (new NavLink('Teams', 'team_index'))
                ->setIcon('microsoft-teams');
        }

        return null;
    }
}
