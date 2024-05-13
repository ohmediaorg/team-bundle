# Installation

Update `composer.json` by adding this to the `repositories` array:

```json
{
    "type": "vcs",
    "url": "https://github.com/ohmediaorg/team-bundle"
}
```

Then run `composer require ohmediaorg/team-bundle:dev-main`.

Import the routes in `config/routes.yaml`:

```yaml
oh_media_team:
    resource: '@OHMediaTeamBundle/config/routes.yaml'
```

Run `php bin/console make:migration` then run the subsequent migration.

# Frontend

The bundle includes templates that output the Teams/FAQs using Bootstrap's
Team component.

If custom output is needed, override the following templates:

1. `templates/bundles/OHMediaTeamBundle/team.html.twig`
1. `templates/bundles/OHMediaTeamBundle/faq.html.twig`
