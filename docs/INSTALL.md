# Install

## Requirements
- PHP 8+
- MySQL/MariaDB
- Web server with mod_rewrite or friendly PHP routing (not required but nice)

## Steps
1. Upload `/site` to your hosting.
2. Edit `/site/db/config.php` with DB credentials and:
   - `ORDER_TO_EMAIL`
   - `WHATSAPP_E164`
   - `BASE_PATH` (e.g. `/maria/site`)
3. (If needed) visit `/site/install.php` to create DB and seed admin user.
4. Go to `/site/admin/` and log in:
   - Default: `admin / admin1234` (change it via DB or password flow if added)
5. Add your dishes in Admin. Upload images (800px variants generated).
6. Test the order flow on the public site.

## File Permissions
- Ensure `/site/uploads` is writable by the web server user.

## Troubleshooting
- 500 error? Check PHP error log.
- Menu images 404? Verify the stored `img` path points under `/maria/site/uploads/...`.
- Stale JS? Bump the version in the footer (v6 already included) or hard refresh.
