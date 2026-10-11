# DhuriyaMart — Database Backup & Disaster Recovery Guide

This operational manual documents automated database backups, media asset preservation, and restoration procedures for **DhuriyaMart** deployed on an Ubuntu VPS.

---

## 1. Backup Strategy Overview

A robust e-commerce backup strategy protects two distinct assets:
1. **Relational Database (`dhuriyamart_production`):** Orders, line item snapshots, seller ledgers, payment transactions, users, products, inventory movements, and audit trails.
2. **Media Assets (`storage/app/public`):** Product variant imagery, shop banners, seller KYC proofs (PAN, GSTIN documents), and dispute attachments.

---

## 2. Automated Daily Backup Script

Create a dedicated backup directory and script on the VPS:

```bash
mkdir -p /home/dhuriyamart/backups/db
mkdir -p /home/dhuriyamart/backups/media
nano /home/dhuriyamart/scripts/backup.sh
```

Paste the following script:

```bash
#!/bin/bash
set -e

# Configuration
APP_DIR="/home/dhuriyamart/htdocs/dhuriyamart.com"
BACKUP_DIR="/home/dhuriyamart/backups"
DATE=$(date +"%Y%m%d_%H%M%S")
RETENTION_DAYS=14

# 1. Read Database Credentials from .env
DB_NAME=$(grep '^DB_DATABASE=' "$APP_DIR/.env" | cut -d '=' -f2 | tr -d '"')
DB_USER=$(grep '^DB_USERNAME=' "$APP_DIR/.env" | cut -d '=' -f2 | tr -d '"')
DB_PASS=$(grep '^DB_PASSWORD=' "$APP_DIR/.env" | cut -d '=' -f2 | tr -d '"')
DB_HOST=$(grep '^DB_HOST=' "$APP_DIR/.env" | cut -d '=' -f2 | tr -d '"')

# 2. Database Backup using mysqldump with InnoDB single-transaction flag
echo "[$(date)] Starting MySQL backup of $DB_NAME..."
DB_FILE="$BACKUP_DIR/db/dhuriyamart_${DATE}.sql.gz"

mysqldump \
  --host="$DB_HOST" \
  --user="$DB_USER" \
  --password="$DB_PASS" \
  --single-transaction \
  --quick \
  --routines \
  --triggers \
  "$DB_NAME" | gzip -9 > "$DB_FILE"

echo "[$(date)] Database backup completed: $DB_FILE ($(du -h "$DB_FILE" | cut -f1))"

# 3. Media Assets Backup (product images & seller documents)
echo "[$(date)] Archiving uploaded media..."
MEDIA_FILE="$BACKUP_DIR/media/media_${DATE}.tar.gz"
tar -czf "$MEDIA_FILE" -C "$APP_DIR/storage/app" public

echo "[$(date)] Media backup completed: $MEDIA_FILE ($(du -h "$MEDIA_FILE" | cut -f1))"

# 4. Prune Backups older than RETENTION_DAYS
echo "[$(date)] Cleaning up backups older than $RETENTION_DAYS days..."
find "$BACKUP_DIR/db" -name "*.sql.gz" -mtime +$RETENTION_DAYS -delete
find "$BACKUP_DIR/media" -name "*.tar.gz" -mtime +$RETENTION_DAYS -delete

echo "[$(date)] Backup cycle completed successfully."
```

Make the script executable:
```bash
chmod +x /home/dhuriyamart/scripts/backup.sh
```

### Schedule the Backup via CloudPanel Cron:
In CloudPanel ➔ **Cron Jobs**, add:
- Schedule: `0 2 * * *` (Every night at 2:00 AM)
- Command:
  ```bash
  /home/dhuriyamart/scripts/backup.sh >> /home/dhuriyamart/backups/backup.log 2>&1
  ```

---

## 3. Database Restoration Procedure

> [!CAUTION]
> Restoring a database will overwrite existing table rows. Always take a manual snapshot of the current state before performing a restoration.

### Step 3.1: Put Application into Maintenance Mode
```bash
cd /home/dhuriyamart/htdocs/dhuriyamart.com
php artisan down --render="errors::503" --secret="dhuriyamart-recovery"
```

### Step 3.2: Take Pre-Restore Safety Snapshot
```bash
mysqldump -u dhuriyamart_dbuser -p dhuriyamart_production | gzip > /home/dhuriyamart/backups/pre_restore_safety.sql.gz
```

### Step 3.3: Restore from Gzipped Backup
To restore from a specific backup file (e.g., `dhuriyamart_20261011_020000.sql.gz`):
```bash
zcat /home/dhuriyamart/backups/db/dhuriyamart_20261011_020000.sql.gz | mysql -u dhuriyamart_dbuser -p dhuriyamart_production
```

### Step 3.4: Restore Media Assets (if applicable)
```bash
tar -xzf /home/dhuriyamart/backups/media/media_20261011_020000.tar.gz -C /home/dhuriyamart/htdocs/dhuriyamart.com/storage/app/
php artisan storage:link
```

### Step 3.5: Clear Caches and Bring Site Online
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

---

## 4. Disaster Recovery to a Fresh Server

If the primary VPS experiences complete failure, follow these steps to restore on a brand new Ubuntu VPS:

1. **Provision New VPS & Install CloudPanel.**
2. **Create PHP Site** (`dhuriyamart.com`) pointing to `/public`.
3. **Create MySQL Database & User** in CloudPanel.
4. **Clone Code Repository & Install Dependencies:**
   ```bash
   git clone https://github.com/your-org/dhuriyamart.git /home/dhuriyamart/htdocs/dhuriyamart.com
   cd /home/dhuriyamart/htdocs/dhuriyamart.com
   composer install --no-dev --optimize-autoloader
   ```
5. **Configure `.env`** with the original `APP_KEY` (critical for decrypting stored credentials) and new database details.
6. **Restore Database Dump:**
   ```bash
   zcat /path/to/latest_backup.sql.gz | mysql -u dhuriyamart_dbuser -p dhuriyamart_production
   ```
7. **Extract Media Archive:**
   ```bash
   tar -xzf /path/to/latest_media.tar.gz -C /home/dhuriyamart/htdocs/dhuriyamart.com/storage/app/
   php artisan storage:link
   ```
8. **Issue SSL Certificate** in CloudPanel and verify domain DNS records.
9. **Start Queue Workers and Cron Jobs.**

---

## 5. Verification Checklist Post-Restoration

- [ ] Run `php artisan test` to verify database schema matches model expectations.
- [ ] Check recent orders in `/admin/orders`.
- [ ] Log in as seller and verify product listings and images are present.
- [ ] Verify that customer balances, settlements, and ledger records tally.
- [ ] Confirm queue worker processes pending tasks without errors.

