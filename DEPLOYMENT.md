# DhuriyaMart — Production Deployment Guide
### Ubuntu VPS with CloudPanel Control Panel

This guide walks through deploying the **DhuriyaMart** full-stack multi-vendor e-commerce marketplace on an **Ubuntu VPS (22.04 or 24.04 LTS)** managed with **CloudPanel**.

---

## 1. Prerequisites & Server Requirements

| Component | Minimum Specification | Recommended Specification |
|---|---|---|
| **Operating System** | Ubuntu 22.04 / 24.04 LTS | Ubuntu 24.04 LTS x86_64 |
| **Control Panel** | CloudPanel v2 | CloudPanel v2.4+ |
| **CPU / RAM** | 2 vCPU / 2 GB RAM | 4 vCPU / 4 GB RAM |
| **PHP Version** | PHP 8.2 | PHP 8.3 or PHP 8.4 |
| **Database** | MySQL 8.0+ / MariaDB 10.11+ | MySQL 8.0 (InnoDB engine) |
| **Web Server** | Nginx with PHP-FPM | Nginx 1.24+ |

### Required PHP Extensions:
Ensure the following extensions are enabled in your CloudPanel PHP configuration:
```bash
bcmath, ctype, curl, dom, fileinfo, gd, intl, json, mbstring, openssl, pdo_mysql, tokenizer, xml, zip
```

---

## 2. Setting Up CloudPanel Site & Database

### Step 2.1: Create PHP Site in CloudPanel
1. Log in to your CloudPanel admin interface (`https://your-server-ip:8443`).
2. Click **+ Add Site** ➔ **Create a PHP Site**.
3. Fill in the site details:
   - **Domain Name:** `dhuriyamart.com` (or your chosen domain/subdomain)
   - **Site User:** `dhuriyamart` (a dedicated non-root Linux user is created)
   - **Site User Password:** Generate a secure password
   - **PHP Version:** Select `PHP 8.3` or `PHP 8.4`
4. Set the **Application Directory / Document Root**:
   ```
   /home/dhuriyamart/htdocs/dhuriyamart.com/public
   ```
   > [!IMPORTANT]
   > The document root MUST point to the `/public` folder of your Laravel installation, NOT the root directory.

### Step 2.2: Create MySQL Database
1. In CloudPanel, navigate to **Databases** ➔ **+ Add Database**.
2. Configure:
   - **Database Name:** `dhuriyamart_production`
   - **Database User:** `dhuriyamart_dbuser`
   - **Password:** Generate a strong random password
3. Save the credentials securely for the `.env` configuration file.

---

## 3. Server-Side Installation & Setup

SSH into your server as the site user (`dhuriyamart`) or as `root`:
```bash
ssh dhuriyamart@your-server-ip
cd /home/dhuriyamart/htdocs/dhuriyamart.com
```

### Step 3.1: Clone Application Code
```bash
# Clone the repository into a temporary directory or directly
git clone https://github.com/your-org/dhuriyamart.git repo
shopt -s dotglob
mv repo/* .
rm -rf repo
```

### Step 3.2: Install Composer Dependencies
Run Composer with production flags to optimize classmaps and exclude dev tools:
```bash
composer install --no-dev --optimize-autoloader --no-interaction
```

### Step 3.3: Configure Production `.env` File
```bash
cp .env.example .env
nano .env
```

Set the following critical production values:
```ini
APP_NAME="DhuriyaMart"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://dhuriyamart.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dhuriyamart_production
DB_USERNAME=dhuriyamart_dbuser
DB_PASSWORD=your_secure_db_password_from_step_2

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public

# Live Razorpay Credentials
RAZORPAY_KEY_ID=rzp_live_xxxxxxxxxxxxxxxx
RAZORPAY_KEY_SECRET=your_live_razorpay_secret
RAZORPAY_WEBHOOK_SECRET=your_configured_webhook_secret
PAYMENT_SANDBOX_MODE=0
COD_ENABLED=1

# Production Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@dhuriyamart.com
MAIL_PASSWORD=your_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="support@dhuriyamart.com"
MAIL_FROM_NAME="DhuriyaMart"
```

Generate the unique application encryption key:
```bash
php artisan key:generate --force
```

### Step 3.4: File Permissions & Ownership
Ensure the web server user can read and write to the cache and storage directories:
```bash
# Set appropriate directory permissions
chmod -R 775 storage bootstrap/cache
```

### Step 3.5: Run Migrations & Database Seeders
```bash
# Run database migrations
php artisan migrate --force

# Seed initial roles, administration accounts, default settings, and categories
php artisan db:seed --force
```

### Step 3.6: Symlink Public Storage
Create the symbolic link linking `storage/app/public` to `public/storage`:
```bash
php artisan storage:link
```

---

## 4. Production Performance Optimization

Optimize Laravel's routes, configuration, and templates for maximum throughput:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## 5. Setting Up Scheduled Tasks (Cron)

DhuriyaMart requires the Laravel scheduler for inventory reconciliations, settlement reminders, and coupon expiries.

In CloudPanel:
1. Navigate to your site: `dhuriyamart.com` ➔ **Cron Jobs**.
2. Click **+ Add Cron Job**.
3. Schedule: `* * * * *` (Every Minute).
4. Command:
   ```bash
   php /home/dhuriyamart/htdocs/dhuriyamart.com/artisan schedule:run >> /dev/null 2>&1
   ```

---

## 6. Background Queue Worker (Supervisor)

For processing asynchronous emails, notifications, and webhooks:

1. Create a Supervisor configuration file:
   ```bash
   sudo nano /etc/supervisor/conf.d/dhuriyamart-worker.conf
   ```
2. Paste the following configuration:
   ```ini
   [program:dhuriyamart-worker]
   process_name=%(program_name)s_%(process_num)02d
   command=php /home/dhuriyamart/htdocs/dhuriyamart.com/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
   autostart=true
   autorestart=true
   stopasgroup=true
   killasgroup=true
   user=dhuriyamart
   numprocs=2
   redirect_stderr=true
   stdout_logfile=/home/dhuriyamart/htdocs/dhuriyamart.com/storage/logs/worker.log
   stopwaitsecs=3600
   ```
3. Update and start Supervisor:
   ```bash
   sudo supervisorctl reread
   sudo supervisorctl update
   sudo supervisorctl start dhuriyamart-worker:*
   ```

---

## 7. SSL Certificate Installation

1. In CloudPanel, go to `dhuriyamart.com` ➔ **SSL/TLS**.
2. Click **+ New Certificate** ➔ **Let's Encrypt**.
3. Ensure your domain's DNS A Record points to the VPS IP address.
4. Click **Create and Install**.
5. Enable **Force HTTPS** toggle.

---

## 8. Nginx Tuning Directives in CloudPanel

In CloudPanel ➔ **Vhost Editor**, add the following optimized blocks inside the `server { ... }` configuration:

```nginx
# Increase file upload limits for product image catalogs & KYC documents
client_max_body_size 64M;

# Static asset aggressive caching
location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff2|woff|ttf|svg)$ {
    expires 30d;
    add_header Cache-Control "public, no-transform";
    access_log off;
}

# Gzip Compression
gzip on;
gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript image/svg+xml;
gzip_min_length 1000;

# Security Headers
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header X-Content-Type-Options "nosniff" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
```

---

## 9. Automated Deployment Script (`deploy.sh`)

Create a deployment script in the project root for zero-downtime updates:

```bash
cat << 'EOF' > /home/dhuriyamart/htdocs/dhuriyamart.com/deploy.sh
#!/bin/bash
set -e

echo "Starting DhuriyaMart Deployment..."

# 1. Put site in maintenance mode
php artisan down --render="errors::503" --secret="dhuriyamart-deploy-pass"

# 2. Pull latest git changes
git pull origin main

# 3. Install composer dependencies
composer install --no-dev --optimize-autoloader --no-interaction

# 4. Run migrations
php artisan migrate --force

# 5. Clear and rebuild caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. Restart queue workers
php artisan queue:restart

# 7. Bring site out of maintenance mode
php artisan up

echo "DhuriyaMart deployed successfully!"
EOF

chmod +x /home/dhuriyamart/htdocs/dhuriyamart.com/deploy.sh
```

---

## 10. Post-Deployment Verification Checklist

- [ ] Storefront loads with valid SSL certificate (`https://dhuriyamart.com`).
- [ ] Product catalogue images render properly from `/storage`.
- [ ] Customer account registration and login works.
- [ ] Super admin login functions at `/admin/dashboard`.
- [ ] Multi-vendor checkout succeeds and splits into separate `seller_orders`.
- [ ] Test Razorpay webhook delivery reaches `/webhooks/payments/razorpay` without CSRF error.
- [ ] Low stock alert thresholds work properly.
- [ ] CloudPanel Cron job executes without errors.
- [ ] Supervisor queue worker is running (`sudo supervisorctl status`).

