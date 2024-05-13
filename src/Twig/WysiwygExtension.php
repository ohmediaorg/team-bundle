<?php

namespace OHMedia\TeamBundle\Twig;

use OHMedia\TeamBundle\Repository\TeamRepository;
use OHMedia\WysiwygBundle\Service\Wysiwyg;
use OHMedia\WysiwygBundle\Twig\AbstractWysiwygExtension;
use Twig\Environment;
use Twig\TwigFunction;

class WysiwygExtension extends AbstractWysiwygExtension
{
    private array $teams = [];

    public function __construct(
        private TeamRepository $teamRepository,
        private Wysiwyg $wysiwyg,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('team', [$this, 'team'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
        ];
    }

    public function team(Environment $twig, int $id = null)
    {
        if (isset($this->teams[$id])) {
            // prevent infinite recursion
            return;
        }

        $this->teams[$id] = true;

        $team = $id ? $this->teamRepository->find($id) : null;

        if (!$team) {
            return '';
        }

        $members = $team->getMembers();

        if (!count($members)) {
            return '';
        }

        foreach ($members as $member) {
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'Person',
                // ...
          ];
        }

        $rendered = $twig->render('@OHMediaTeam/team.html.twig', [
            'team' => $team,
        ]);

        $rendered .= '<script type="application/ld+json">'.json_encode($schema).'</script>';

        return $rendered;
    }
}
