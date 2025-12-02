# Local Development Setup Guide for lukasik.ua

## Prerequisites

### Required Software
1. **PHP 7.3+** (Recommended: PHP 7.4 or 8.0)
2. **MySQL/MariaDB 5.6+**
3. **Composer** (optional, for dependencies)
4. **Redis** (optional, but configured in production)

### macOS Setup (Recommended)

#### Option 1: Using Built-in PHP + MySQL
```bash
# Check PHP version (should be 7.3+)
php -v

# Install MySQL via Homebrew
brew install mysql
brew services start mysql

# Secure MySQL installation
mysql_secure_installation
```

#### Option 2: Using MAMP/XAMPP
Download and install [MAMP](https://www.mamp.info/) or [XAMPP](https://www.apachefriends.org/)

#### Option 3: Using Docker (Recommended)
See Docker setup section below.

---

## Setup Steps

### Step 1: Database Setup

1. **Create Local Database**
```bash
# Login to MySQL
mysql -u root -p

# Create database
CREATE DATABASE lukasik_local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Create user (optional)
CREATE USER 'opencart'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON lukasik_local.* TO 'opencart'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

2. **Import Production Database**
```bash
# Import the backup
mysql -u root -p lukasik_local < database_backup_2025-11-26_23-08-12.sql

# This may take a few minutes due to the size
```

3. **Update Database URLs**
```sql
# Login to MySQL
mysql -u root -p lukasik_local

# Update store URLs
UPDATE oc_setting SET value = 'http://localhost:8000/' WHERE `key` = 'config_url';
UPDATE oc_setting SET value = 'http://localhost:8000/' WHERE `key` = 'config_ssl';

# Clear cache settings if needed
DELETE FROM oc_setting WHERE `key` LIKE 'config_cache%';

# Exit
EXIT;
```

### Step 2: Configure Application

1. **Update Admin Config**
```bash
# Rename the local config to active config
cp admin/config-local.php admin/config.php
```

Or manually edit `/admin/config.php` with your database credentials.

2. **Update Main Config**
The root `config.php` is already configured for local development.

**Important**: Update database credentials in both files:
- `/config.php` (frontend)
- `/admin/config.php` (admin panel)

```php
define('DB_USERNAME', 'root');           // Your MySQL username
define('DB_PASSWORD', '');                // Your MySQL password
define('DB_DATABASE', 'lukasik_local');   // Database name
```

### Step 3: Set Permissions

```bash
cd /Users/sashachekh/Desktop/www

# Set write permissions for storage directories
chmod -R 755 storage/
chmod -R 755 image/
chmod -R 755 system/storage/

# If you encounter permission issues
chmod -R 777 storage/
chmod -R 777 image/cache/
chmod -R 777 system/storage/cache/
```

### Step 4: Start Development Server

#### Option 1: PHP Built-in Server (Quickest)
```bash
cd /Users/sashachekh/Desktop/www
php -S localhost:8000
```

Then access:
- **Frontend**: http://localhost:8000/
- **Admin Panel**: http://localhost:8000/admin/

#### Option 2: Using MAMP/XAMPP
1. Move project to htdocs folder
2. Configure virtual host
3. Access via http://localhost/www/

#### Option 3: Using Apache/Nginx
Configure virtual host (see Virtual Host section below)

---

## Docker Setup (Recommended)

Create `docker-compose.yml` in project root:

```yaml
version: '3.8'

services:
  mysql:
    image: mysql:8.0
    container_name: lukasik_mysql
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: lukasik_local
    ports:
      - "3306:3306"
    volumes:
      - ./database_backup_2025-11-26_23-08-12.sql:/docker-entrypoint-initdb.d/dump.sql
      - mysql_data:/var/lib/mysql

  php:
    image: php:7.4-apache
    container_name: lukasik_php
    ports:
      - "8000:80"
    volumes:
      - .:/var/www/html
    depends_on:
      - mysql
    environment:
      - DB_HOST=mysql
      - DB_USER=root
      - DB_PASSWORD=root
      - DB_NAME=lukasik_local

  redis:
    image: redis:alpine
    container_name: lukasik_redis
    ports:
      - "6379:6379"

volumes:
  mysql_data:
```

Start with Docker:
```bash
docker-compose up -d
```

---

## Post-Setup Configuration

### 1. Clear Cache
```bash
# Remove cache files
rm -rf storage/cache/*
rm -rf system/storage/cache/*

# Or via admin panel: Extensions > Modifications > Refresh
```

### 2. Admin Panel Access

**Default admin URL**: http://localhost:8000/admin/

You'll need to find credentials in the database:
```sql
# Get admin users
SELECT * FROM oc_user;

# If needed, reset admin password
UPDATE oc_user SET password = MD5('admin') WHERE user_id = 1;
```

Or create a new admin user:
```sql
INSERT INTO oc_user (user_group_id, username, password, firstname, lastname, email, status, date_added) 
VALUES (1, 'admin', MD5('admin'), 'Admin', 'User', 'admin@localhost.com', 1, NOW());
```

### 3. Disable Production Features

In admin panel or database:
```sql
# Disable live payment gateways
UPDATE oc_setting SET value = '0' WHERE `key` LIKE '%payment%status%';

# Enable developer mode (if available)
UPDATE oc_setting SET value = '1' WHERE `key` = 'config_error_display';
```

---

## Common Issues & Solutions

### Issue 1: "Permission Denied" Errors
```bash
# Fix permissions
sudo chmod -R 777 storage/
sudo chmod -R 777 image/cache/
```

### Issue 2: MySQL Connection Failed
- Check MySQL is running: `mysql.server status` or `brew services list`
- Verify credentials in `config.php`
- Check port (default: 3306)

### Issue 3: White Screen / Fatal Error
```bash
# Enable error display
# Edit: system/config/default.php

error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Issue 4: Modifications Not Working
```bash
# Clear modifications cache
rm -rf storage/modification/*
rm -rf system/storage/modification/*

# Refresh via admin: Extensions > Modifications > Refresh
```

### Issue 5: Images Not Loading
```bash
# Check image directory permissions
chmod -R 755 image/

# Clear image cache
rm -rf image/cache/*
```

### Issue 6: Redis Not Available
If you don't have Redis installed, comment out Redis config:
```php
// In config.php and admin/config.php
// define('CACHE_HOSTNAME', 'localhost');
// define('CACHE_PORT', 6379);
```

---

## Development Tips

### 1. Enable Debug Mode
Edit `system/config/default.php`:
```php
$_['error_display'] = 1;
$_['error_log'] = 1;
```

### 2. Watch Logs
```bash
tail -f storage/logs/error.log
```

### 3. Database Changes
Always test on local DB first, then export changes:
```bash
# Export only structure
mysqldump -u root -p --no-data lukasik_local > structure.sql

# Export specific table
mysqldump -u root -p lukasik_local oc_your_table > table_backup.sql
```

### 4. Version Control (Recommended)
```bash
# Initialize git
git init
git add .
git commit -m "Initial local setup"

# Create development branch
git checkout -b feature/your-feature-name
```

---

## Virtual Host Configuration (Optional)

### Apache Virtual Host
Edit: `/etc/apache2/extra/httpd-vhosts.conf`

```apache
<VirtualHost *:80>
    ServerName lukasik.local
    DocumentRoot "/Users/sashachekh/Desktop/www"
    
    <Directory "/Users/sashachekh/Desktop/www">
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog "/var/log/apache2/lukasik-error.log"
    CustomLog "/var/log/apache2/lukasik-access.log" common
</VirtualHost>
```

Add to `/etc/hosts`:
```
127.0.0.1 lukasik.local
```

Restart Apache:
```bash
sudo apachectl restart
```

---

## Useful Commands

```bash
# Check PHP extensions
php -m

# Test PHP configuration
php -i | grep -i "mysqli\|pdo\|curl\|zip\|gd"

# MySQL quick access
mysql -u root -p lukasik_local

# Find PHP config file
php --ini

# Check Apache/web server status
sudo apachectl status

# Restart MySQL
brew services restart mysql
```

---

## Project Structure

```
www/
├── admin/               # Admin panel
├── catalog/             # Frontend application
├── image/               # Product images
├── storage/             # Writable storage (cache, logs, sessions)
├── system/              # Core framework
├── config.php           # Main configuration
├── index.php            # Frontend entry point
└── .htaccess           # Apache rewrite rules
```

---

## Next Steps

1. ✅ Setup database
2. ✅ Configure files
3. ✅ Set permissions
4. ✅ Start server
5. ✅ Access admin panel
6. ✅ Clear cache
7. 🚀 Start developing!

---

## Support

- **OpenCart Documentation**: https://docs.opencart.com/
- **OCFilter Docs**: https://ocfilter.com/
- **Community**: https://opencartforum.com/

---

**Note**: This is a development environment. Never use these configurations in production!

