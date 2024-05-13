<?php

namespace OHMedia\TeamBundle\Security\Voter;

use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Security\Voter\AbstractEntityVoter;
use OHMedia\TeamBundle\Entity\TeamMember;

class TeamMemberVoter extends AbstractEntityVoter
{
    public const CREATE = 'create';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    protected function getAttributes(): array
    {
        return [
            self::CREATE,
            self::EDIT,
            self::DELETE,
        ];
    }

    protected function getEntityClass(): string
    {
        return TeamMember::class;
    }

    protected function canCreate(TeamMember $teamMember, User $loggedIn): bool
    {
        return true;
    }

    protected function canEdit(TeamMember $teamMember, User $loggedIn): bool
    {
        return true;
    }

    protected function canDelete(TeamMember $teamMember, User $loggedIn): bool
    {
        return true;
    }
}
