# Contributing

Thanks for even considering helping. Here’s how not to make a mess:

## Development setup
- PHP 8+
- MySQL 5.7+ (or MariaDB equivalent)
- Configure `site/db/config.php`

## Branches
- `main` is protected. Make a branch: `feature/<short-name>` or `fix/<short-name>`.

## Style
- PHP: follow PSR-12-ish. Keep functions short, escape output.
- JS: vanilla, no frameworks. Keep modules small.
- CSS: keep it minimal and readable.

## Commits
- Present tense, imperative: `Add`, `Fix`, `Update`, `Remove`.

## Pull Requests
- Link to an issue (or explain “why”).
- Include screenshots for UI changes.
- Run lints: `php -l $(git ls-files '*.php')`.

## Security
- See `SECURITY.md`. Do not open public PRs with exploit details.
