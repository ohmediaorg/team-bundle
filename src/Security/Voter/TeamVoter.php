<?php

namespace OHMedia\TeamBundle\Security\Voter;

use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Security\Voter\AbstractEntityVoter;
use OHMedia\TeamBundle\Entity\Team;
use OHMedia\WysiwygBundle\Service\Wysiwyg;

class TeamVoter extends AbstractEntityVoter
{
    public const INDEX = 'index';
    public const CREATE = 'create';
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    public function __construct(private Wysiwyg $wysiwyg)
    {
    }

    protected function getAttributes(): array
    {
        return [
            self::INDEX,
            self::CREATE,
            self::VIEW,
            self::EDIT,
            self::DELETE,
        ];
    }

    protected function getEntityClass(): string
    {
        return Team::class;
    }

    protected function canIndex(Team $team, User $loggedIn): bool
    {
        return true;
    }

    protected function canCreate(Team $team, User $loggedIn): bool
    {
        return true;
    }

    protected function canView(Team $team, User $loggedIn): bool
    {
        return true;
    }

    protected function canEdit(Team $team, User $loggedIn): bool
    {
        return true;
    }

    protected function canDelete(Team $team, User $loggedIn): bool
    {
        $shortcode = sprintf('{{ team(%d) }}', $team->getId());

        return !$this->wysiwyg->shortcodesInUse($shortcode);
    }
}
