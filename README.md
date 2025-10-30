# Restaurante da Dona Maria

Simple PHP site for a small restaurant.

## Features
- Public menu with responsive images (800px variants)
- Multi-item order modal that opens WhatsApp and sends email
- Admin panel: CRUD for dishes, image upload with auto-resize, tags, featured
- Cache-busted JS (v6) so clients don’t get stale scripts

## Demo
Screenshots go in [`/docs/screens`](docs/screens). Add some when you’re done gloating.

## Quick start
1. Upload `/site` to your host (PHP 8+).
2. Edit `/site/db/config.php` (DB creds, email, WhatsApp, BASE_PATH).
3. Visit `/site/install.php` if DB tables aren’t created.
4. Go to `/site/admin/`.  
   Login: `admin / admin1234` (change it immediately).
5. Add dishes and images. Done.

## Tech
- PHP 8+, PDO (MySQL)
- Vanilla JS
- Lightweight CSS

## Releases
See [CHANGELOG](CHANGELOG.md). You can grab packaged zips on the Releases page.

## Contributing
See [CONTRIBUTING](CONTRIBUTING.md). PRs welcome, chaos not.

## Security
Report vulnerabilities privately. See [SECURITY](SECURITY.md).

## License
[MIT](LICENSE)
