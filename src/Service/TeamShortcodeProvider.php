<?php

namespace OHMedia\TeamBundle\Service;

use OHMedia\BackendBundle\Shortcodes\AbstractShortcodeProvider;
use OHMedia\BackendBundle\Shortcodes\Shortcode;
use OHMedia\TeamBundle\Repository\TeamRepository;

class TeamShortcodeProvider extends AbstractShortcodeProvider
{
    public function __construct(private TeamRepository $teamRepository)
    {
    }

    public function getTitle(): string
    {
        return 'Teams';
    }

    public function buildShortcodes(): void
    {
        $teams = $this->teamRepository->createQueryBuilder('t')
            ->orderBy('t.name', 'asc')
            ->getQuery()
            ->getResult();

        foreach ($teams as $team) {
            $id = $team->getId();

            $this->addShortcode(new Shortcode(
                sprintf('%s (ID:%s)', $team, $id),
                'team('.$id.')'
            ));
        }
    }
}
