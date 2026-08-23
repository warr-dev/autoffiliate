# Hostinger Deployment Guide (Autoffiliate)

This guide provides step-by-step instructions for deploying **Autoffiliate** (Laravel 13 + Svelte 5 + Inertia v3 + Tailwind CSS v4) on **Hostinger VPS** (Recommended) or **Hostinger Cloud / Web Hosting (hPanel)**.

---

## 📋 System Requirements

- **PHP:** 8.3+ (Required extensions: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pcre`, `pdo_mysql`, `tokenizer`, `xml`)
- **Web Server:** Nginx or Apache
- **Database:** MariaDB 10.6+ or MySQL 8.0+
- **Node.js & NPM:** Node.js 20+ LTS & npm 10+
- **Process Manager / Cron:** Supervisor or systemd (VPS) / hPanel Cron Jobs (Shared/Cloud)
- **SSL Certificate:** Free Let's Encrypt SSL

---

## 🚀 Method 1: Deploying on Hostinger VPS (Ubuntu 22.04 / 24.04) — Recommended

Hostinger VPS provides full root SSH access and allows running background daemons (`schedule:work` and `queue:work` via Supervisor), making it the optimal choice for background automation.

### Step 1: Initial Server Setup & Dependencies
Connect via SSH to your VPS:
```bash
ssh root@YOUR_SERVER_IP
```

Update system and install required packages:
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl git unzip ufw redis-server supervisor nginx

# Add PHP 8.3 repository & install PHP + extensions
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-curl php8.3-gd \
    php8.3-mbstring php8.3-xml php8.3-zip php8.3-bcmath php8.3-intl php8.3-redis

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js 20 LTS & npm
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

---

### Step 2: Configure Database (MariaDB / MySQL)
```bash
sudo apt install -y mariadb-server
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```
Inside MySQL prompt:
```sql
CREATE DATABASE autoffiliate CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'autoffiliate_user'@'localhost' IDENTIFIED BY 'StrongDBPasswordHere!';
GRANT ALL PRIVILEGES ON autoffiliate.* TO 'autoffiliate_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

### Step 3: Clone & Install Application
```bash
cd /var/www
sudo git clone https://github.com/YOUR_REPO/autoffiliate.git /var/www/autoffiliate
cd /var/www/autoffiliate

# Install PHP dependencies (production optimized)
sudo composer install --no-dev --optimize-autoloader

# Install Node dependencies and build frontend
sudo npm ci
sudo npm run build

# Configure environment file
sudo cp .env.example .env
sudo nano .env
```

Set the following in `.env`:
```ini
APP_NAME=Autoffiliate
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=autoffiliate
DB_USERNAME=autoffiliate_user
DB_PASSWORD=StrongDBPasswordHere!

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_SECURE_COOKIE=true
```

Run initialization commands:
```bash
# Generate app key
sudo php artisan key:generate

# Run migrations and seed default data
sudo php artisan migrate --seed --force

# Create admin user
sudo php artisan make:user admin@yourdomain.com "Admin User" --password="YourSecureAdminPassword"

# Optimize Laravel cache
sudo php artisan config:cache
sudo php artisan route:cache
sudo php artisan view:cache
sudo php artisan wayfinder:generate

# Set permissions
sudo chown -R www-data:www-data /var/www/autoffiliate
sudo chmod -R 775 /var/www/autoffiliate/storage /var/www/autoffiliate/bootstrap/cache
```

---

### Step 4: Configure Nginx Web Server
Create Nginx site configuration:
```bash
sudo nano /etc/nginx/sites-available/autoffiliate
```

Paste the configuration below (replace `yourdomain.com` with your domain):
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/autoffiliate/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    index index.php index.html;
    charset utf-8;

    # Gzip Compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site and restart Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/autoffiliate /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

### Step 5: Install Free SSL Certificate (HTTPS)
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

---

### Step 6: Configure Background Automated Scheduler & Queue Worker (Supervisor)

To ensure automated posts trigger without keeping any browser tab open, configure **Supervisor**:

Create `/etc/supervisor/conf.d/autoffiliate-scheduler.conf`:
```ini
[program:autoffiliate-scheduler]
process_name=%(program_name)s
command=/usr/bin/php /var/www/autoffiliate/artisan schedule:work
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/autoffiliate/storage/logs/scheduler-supervisor.log
stopwaitsecs=60
```

Create `/etc/supervisor/conf.d/autoffiliate-worker.conf`:
```ini
[program:autoffiliate-worker]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php /var/www/autoffiliate/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/autoffiliate/storage/logs/worker-supervisor.log
stopwaitsecs=3600
```

Start the Supervisor daemons:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
sudo supervisorctl status
```

---

## 🌐 Method 2: Deploying on Hostinger Cloud / Shared Web Hosting (hPanel)

If using Hostinger's managed Web Hosting or Cloud Hosting plan with **hPanel**:

### Step 1: Set PHP Version
1. Log in to **Hostinger hPanel**.
2. Navigate to **Websites ➔ Dashboard ➔ Advanced ➔ PHP Configuration**.
3. Select **PHP 8.3** and enable extensions: `curl`, `fileinfo`, `mbstring`, `openssl`, `pdo_mysql`, `zip`, `bcmath`.

### Step 2: Set Document Root
1. In hPanel, go to **Websites ➔ Manage ➔ Domains ➔ Subdomains / Redirects** or Edit Website Directory.
2. Set the public root folder to `/public_html/public` (or place the Laravel application in a folder above `public_html` and symlink `public` to `public_html`).

### Step 3: Setup MySQL Database in hPanel
1. In hPanel, go to **Databases ➔ Management**.
2. Create a new MySQL database (e.g. `u123456789_autoaff`) and user.
3. Note the Database Name, Username, and Password.

### Step 4: Deploy Files via Git / SSH

#### 💡 Node.js Workarounds (Choose One):

**Option A: Pre-Built Assets from Git (Recommended — NO Node.js Needed on Server!)**
The Git repository now includes pre-compiled production assets in `public/build/`. You do **NOT** need Node.js or `npm` installed on Hostinger!
```bash
cd ~/domains/yourdomain.com
git clone https://github.com/warr-dev/autoffiliate.git app
cd app
composer install --no-dev --optimize-autoloader
cp .env.example .env
```
*(Skip `npm run build` completely! The frontend assets are already pre-compiled).*

**Option B: Install Node.js on Hostinger via NVM (If you want to build on server)**
If you want `node` and `npm` directly in your Hostinger SSH terminal:
```bash
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.7/install.sh | bash
source ~/.bashrc
nvm install 20
node -v   # Returns v20.x.x
npm -v    # Returns 10.x.x
npm ci && npm run build
```

---

### Step 5: Configure `.env` & Run Migrations
1. Edit `.env` with your hPanel database credentials:
   ```bash
   nano .env
   ```
2. Initialize database and optimize Laravel:
   ```bash
   php artisan key:generate
   php artisan migrate --seed --force
   php artisan make:user admin@yourdomain.com "Admin User" --password="YourSecurePassword"
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### Step 6: Configure hPanel Automated Cron Job
To run automated workflows in the background without browser tabs open:
1. In hPanel, go to **Advanced ➔ Cron Jobs**.
2. Select **Custom** command.
3. Set schedule to **Every Minute** (`* * * * *`).
4. Command:
   ```bash
   cd /home/u123456789/domains/yourdomain.com/app && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
   ```
5. Click **Save**.

---

## ⚡ 1-Click Zero-Downtime Deployment Script (`deploy.sh`)

Create `deploy.sh` in your project root for future Git updates:

```bash
#!/bin/bash
set -e

echo "🚀 Starting deployment..."

# Enter maintenance mode
php artisan down || true

# Pull latest changes
git pull origin main

# Install composer dependencies
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Build frontend assets
npm ci
npm run build

# Run migrations
php artisan migrate --force

# Clear and rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan wayfinder:generate

# Restart queue workers & scheduler
if command -v supervisorctl &> /dev/null; then
    sudo supervisorctl restart autoffiliate-worker:*
    sudo supervisorctl restart autoffiliate-scheduler
fi

# Exit maintenance mode
php artisan up

echo "✅ Deployment completed successfully!"
```

Make it executable:
```bash
chmod +x deploy.sh
```

To deploy any update in the future, simply run:
```bash
./deploy.sh
```
