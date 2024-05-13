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

Create `templates/bundles/OHMediaTeamBundle/team.html.twig` which will have access
to a `team` variable. The members can be looped on:

```twig
{% for member in team.members %}
{{ dump(member) }}
{% endfor %}
```
