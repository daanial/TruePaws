# AGENTS.md

## Cursor Cloud specific instructions

### Project overview

TruePaws is a WordPress plugin providing a kennel management system for breeders. It has a PHP backend (WordPress REST API) and a React 18 SPA frontend built with Webpack.

The **active plugin code** lives in `truepaws/` (v1.1.0). The root-level `includes/` and `truepaws.php` are an older copy (v1.0.2) — do not use the root-level files as the plugin entry point.

### Development environment

- **WordPress**: installed at `/var/www/html/wordpress` with Apache on port 80
- **Database**: MariaDB with database `wordpress`, user `wpuser`, password `wppass123`
- **Plugin symlink**: `/var/www/html/wordpress/wp-content/plugins/truepaws -> /workspace/truepaws`
- **WordPress admin**: username `admin`, password `admin123`
- **Auto-login**: `http://localhost/wordpress/dev-login.php` logs in and redirects to TruePaws (avoids manual login flow)
- **Assets symlink**: `/workspace/assets -> /workspace/truepaws/assets` exists because the root-level `includes/class-admin-menu.php` checks for `assets/build/main.js` relative to the plugin dir

### Key gotchas

1. **Two admin-menu PHP files**: `includes/class-admin-menu.php` (root, uses `truepawsConfig`) vs `truepaws/includes/class-admin-menu.php` (uses `truepawsData`). The JS client expects `truepawsData`. The plugin must be symlinked from `truepaws/` not the workspace root.

2. **Freemius SDK**: The secret key is configured in `wp-config.php` as `WP_FS__truepaws_SECRET_KEY`. On first activation, Freemius may show a license activation modal. Skip it for dev with:
   ```bash
   cd /var/www/html/wordpress && wp eval 'if(function_exists("tru_fs")){tru_fs()->skip_connection(null,true);echo "skipped\n";}' --allow-root
   ```

3. **CSS loading**: In dev mode (`webpack --mode=development`), CSS is injected via JS (style-loader). In production mode (`npm run build`), CSS is extracted to `main.css` but **not enqueued by the PHP** — use dev mode builds for local testing.

4. **Services to start**: Before testing, ensure MariaDB and Apache are running:
   ```bash
   sudo service mariadb start
   sudo service apache2 start
   ```

### Build & run commands

- **Install deps**: `cd truepaws && npm install`
- **Dev build**: `cd truepaws && npx webpack --mode=development`
- **Dev watch**: `cd truepaws && npm run dev` (webpack watch mode)
- **Production build**: `cd truepaws && npm run build`
- **App URL**: `http://localhost/wordpress/wp-admin/admin.php?page=truepaws`

### No linting tools configured

The project does not include ESLint, PHPStan, or PHPCS. The webpack build (`npm run build`) is the primary code quality check.
