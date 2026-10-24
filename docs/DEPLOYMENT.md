# Production Deployment & Hosting Guide — Apartment Management System

This guide outlines the production deployment procedure for the **Apartment & Accommodation Management System** (Laravel 12 backend) and the **SmartNest Mobile App** (Flutter Android).

---

## 1. Production Architecture Overview

- **Production Domain**: `https://www.reliancehomes.co.tz`
- **Verified REST API Base URL**: `https://www.reliancehomes.co.tz/api/v1`
- **Backend Framework**: Laravel 12 (PHP ^8.2)
- **Database Engine**: MySQL 8.0+ / MariaDB 10.4+
- **Frontend / Assets**: Blade Templates + Alpine.js + Tailwind CSS (compiled via Vite)
- **Mobile Framework**: Flutter 3.10+ (Android `minSdk 21`)

---

## 2. Server & PHP Prerequisites

Ensure your hosting environment (cPanel, DirectAdmin, or VPS) has:
- **PHP Version**: `8.2` or `8.3`
- **Required PHP Extensions**:
  - `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pcre`, `pdo_mysql`, `tokenizer`, `xml`, `gd` (for PDF/image rendering)
- **Web Server**: Apache with `mod_rewrite` enabled or Nginx with URL rewriting to `public/index.php`.

---

## 3. Directory Layout & Document Root Setup

### Option A: Standard cPanel / Shared Hosting
1. If your domain's document root points to `public_html/`:
   - Place all backend files in `public_html/` or a subdirectory above it.
   - The repository includes a root `.htaccess` file that safely redirects requests from the domain root to `/public/` while securing sensitive folders (`.env`, `storage`, `app`).
2. Verify that visiting `https://www.reliancehomes.co.tz` loads the accommodation homepage without directory listing.

### Option B: VPS / Dedicated Web Server (Recommended)
Set the Apache VirtualHost / Nginx Server block document root directly to the Laravel `public/` folder:
```nginx
server {
    server_name www.reliancehomes.co.tz reliancehomes.co.tz;
    root /var/www/apartments/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 4. Production Environment Configuration (`.env`)

Create your `.env` file directly on the production server (never commit `.env` to Git):

```ini
APP_NAME="SmartNest Apartments"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_APP_KEY
APP_DEBUG=false
APP_URL=https://www.reliancehomes.co.tz

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=YOUR_PROD_DB_NAME
DB_USERNAME=YOUR_PROD_DB_USER
DB_PASSWORD="YOUR_PROD_DB_PASSWORD"

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=public
QUEUE_CONNECTION=database
CACHE_STORE=database

SANCTUM_STATEFUL_DOMAINS=www.reliancehomes.co.tz,reliancehomes.co.tz
```

---

## 5. Deployment Step-by-Step Procedure

### Step 1: Install Composer Dependencies
Run on the server or in your deployment pipeline:
```bash
composer install --no-dev --optimize-autoloader --no-interaction
```

### Step 2: Storage Permissions and Symlink
Ensure the web server user has write permissions to `storage` and `bootstrap/cache`:
```bash
chmod -R 775 storage bootstrap/cache
php artisan storage:link
```

### Step 3: Run Safe Incremental Migrations
Execute pending migrations without destroying existing data:
```bash
php artisan migrate --force
```
> **Warning**: Never run `migrate:fresh` or `db:wipe` on a production server.

### Step 4: Compile Frontend Assets
If Node.js and npm are available on the server:
```bash
npm ci
npm run build
```
*(Alternatively, commit the compiled `public/build/` directory as maintained in this repository).*

### Step 5: Optimize Laravel Caches
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 6. Verifying Production Deployment

Test the live endpoints to ensure health and proper routing:

1. **API Health Ping**:
   ```bash
   curl -i https://www.reliancehomes.co.tz/api/v1/ping
   ```
   *Expected Response:*
   ```json
   {"success":true,"message":"Property Rental System API v1 is live.","timestamp":"..."}
   ```

2. **Public Properties Feed**:
   ```bash
   curl -i https://www.reliancehomes.co.tz/api/v1/properties
   ```
   *Expected Response (HTTP 200 with JSON payload).*

---

## 7. Flutter Mobile App Configuration & Distribution

1. **Verified Base URL**:
   The mobile app default production endpoint in `mobile/lib/core/constants.dart` is:
   `https://www.reliancehomes.co.tz/api/v1`

2. **Build the Release APK**:
   ```powershell
   cd mobile
   flutter pub get
   flutter analyze --no-fatal-infos
   flutter test
   flutter build apk --release
   ```

3. **Output Location**:
   `mobile/build/app/outputs/flutter-apk/app-release.apk`

4. **Runtime Server Switcher**:
   On the mobile login screen, an optional server configuration modal is accessible via the network icon on the top app bar or the URL indicator at the bottom. This allows manual override to local testing IPs if needed without re-building the APK.
