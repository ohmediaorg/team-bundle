<?php

namespace OHMedia\TeamBundle\Twig;

use OHMedia\FileBundle\Service\FileManager;
use OHMedia\SettingsBundle\Service\Settings;
use OHMedia\TeamBundle\Entity\TeamMember;
use OHMedia\TeamBundle\Repository\TeamRepository;
use OHMedia\WysiwygBundle\Service\Wysiwyg;
use OHMedia\WysiwygBundle\Twig\AbstractWysiwygExtension;
use Symfony\Component\HttpFoundation\UrlHelper;
use Twig\Environment;
use Twig\TwigFunction;

class WysiwygExtension extends AbstractWysiwygExtension
{
    private array $teams = [];

    public function __construct(
        private FileManager $fileManager,
        private Settings $settings,
        private TeamRepository $teamRepository,
        private UrlHelper $urlHelper,
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

        $schemaOrganizationName = $this->settings->get('schema_organization_name');

        $rendered = $twig->render('@OHMediaTeam/team.html.twig', [
            'team' => $team,
        ]);

        foreach ($members as $member) {
            $schema = $this->getTeamMemberSchema($member);

            if ($schemaOrganizationName) {
                $schema['worksFor'] = [
                    '@type' => 'Organization',
                    'name' => $schemaOrganizationName,
                ];
            }

            $rendered .= '<script type="application/ld+json">'.json_encode($schema).'</script>';
        }

        return $rendered;
    }

    private function getTeamMemberSchema(TeamMember $teamMember): array
    {
        $schema = [
            '@context' => 'https://schema.org/',
            '@type' => 'Person',
            'name' => (string) $teamMember,
        ];

        if ($honorific = $teamMember->getHonorific()) {
            $schema['honorificPrefix'] = $honorific;
        }

        if ($designation = $teamMember->getDesignation()) {
            $schema['honorificSuffix'] = $designation;
        }

        if ($title = $teamMember->getTitle()) {
            $schema['jobTitle'] = $title;
        }

        if ($bio = $teamMember->getBio()) {
            $schema['description'] = $bio;
        }

        $image = $teamMember->getImage();

        if ($image && $image->getPath()) {
            $webPath = $this->fileManager->getWebPath($image);

            $schema['image'] = $this->urlHelper->getAbsoluteUrl($webPath);
        }

        if ($email = $teamMember->getEmail()) {
            $schema['email'] = $email;
        }

        if ($phone = $teamMember->getPhone()) {
            $schema['telephone'] = $phone;
        }

        $socials = [];

        if ($facebook = $teamMember->getFacebook()) {
            $socials[] = $facebook;
        }

        if ($twitter = $teamMember->getTwitter()) {
            $socials[] = $twitter;
        }

        if ($instagram = $teamMember->getInstagram()) {
            $socials[] = $instagram;
        }

        if ($linkedIn = $teamMember->getLinkedIn()) {
            $socials[] = $linkedIn;
        }

        if ($socials) {
            $schema['sameAs'] = $socials;
        }

        return $schema;
    }
}
