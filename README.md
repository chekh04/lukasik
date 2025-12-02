# lukasik.ua - E-commerce Platform

OpenCart 3.0.3.7 (ocStore) based e-commerce platform with custom theme and extensions.

## 🚀 Quick Start

### Option 1: Docker (Recommended - Easiest)

```bash
# Run the setup script
./setup.sh docker

# Wait for containers to start (2-3 minutes for database import)
docker-compose logs -f mysql

# Once ready, update database URLs
docker-compose exec mysql mysql -u opencart -popencart lukasik_local \
  -e "UPDATE oc_setting SET value = 'http://localhost:8000/' WHERE \`key\` = 'config_url';"

# Access the site
# Frontend: http://localhost:8000
# Admin:    http://localhost:8000/admin
# phpMyAdmin: http://localhost:8080
```

### Option 2: Native PHP + MySQL

```bash
# Run the setup script
./setup.sh native

# Follow the on-screen instructions to:
# 1. Create database
# 2. Import SQL dump
# 3. Update database URLs
# 4. Start PHP server

# Quick version:
mysql -u root -p -e "CREATE DATABASE lukasik_local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p lukasik_local < database_backup_2025-11-26_23-08-12.sql
mysql -u root -p lukasik_local -e "UPDATE oc_setting SET value = 'http://localhost:8000/' WHERE \`key\` = 'config_url';"
php -S localhost:8000
```

## 📚 Full Documentation

See [SETUP_LOCAL.md](SETUP_LOCAL.md) for complete setup instructions, troubleshooting, and development tips.

## 🏗️ Project Structure

```
www/
├── admin/              # Admin panel
├── catalog/            # Frontend storefront
├── image/              # Product images (31,764 files)
├── storage/            # Writable storage (cache, logs, sessions)
├── system/             # Core framework & libraries
├── config.php          # Main configuration
├── index.php           # Frontend entry point
├── docker-compose.yml  # Docker setup
└── setup.sh           # Automated setup script
```

## 🔑 Default Access

After setup, reset admin password:

```sql
# Connect to database
mysql -u root -p lukasik_local

# Reset password to "admin"
UPDATE oc_user SET password = MD5('admin') WHERE user_id = 1;
```

Or get existing credentials:

```sql
SELECT username, email FROM oc_user;
```

## 🛠️ Key Technologies

- **Backend**: PHP 7.3+, OpenCart 3.0.3.7
- **Database**: MySQL 8.0 / MariaDB
- **Cache**: Redis
- **Template**: Twig
- **Theme**: Custom "upstore" theme
- **Extensions**: OCFilter 4.8.2, Blog system, Google Translate, Payment gateways

## 📦 Features

- ✅ Multi-language support (Ukrainian, Russian, English)
- ✅ Advanced product filtering (OCFilter)
- ✅ Custom blog/news system
- ✅ Multiple payment gateways
- ✅ SEO optimized
- ✅ Responsive design
- ✅ Redis caching
- ✅ Asset minification

## 🧪 Development

```bash
# Clear cache
rm -rf storage/cache/*

# Watch logs
tail -f storage/logs/error.log

# Restart Docker services
docker-compose restart

# Stop Docker services
docker-compose down
```

## 🐛 Common Issues

### Permission Errors
```bash
chmod -R 777 storage/
chmod -R 777 image/cache/
```

### MySQL Connection Failed
- Check credentials in `config.php` and `admin/config.php`
- Verify MySQL is running: `mysql.server status`

### White Screen
Enable error display in `system/config/default.php`:
```php
$_['error_display'] = 1;
```

### Images Not Loading
```bash
chmod -R 755 image/
rm -rf image/cache/*
```

## 📝 Environment Files

- `config.php` - Frontend configuration (local)
- `admin/config.php` - Admin configuration (local)
- `config-docker.php` - Docker frontend config
- `admin/config-docker.php` - Docker admin config
- `admin/config.php.production` - Original production config (backup)

## 🔒 Security Notes

⚠️ **Important**: This is a development setup
- Never use these configurations in production
- Always use strong passwords
- Keep software updated
- Backup regularly

## 📖 Documentation Links

- [OpenCart Docs](https://docs.opencart.com/)
- [OCFilter Docs](https://ocfilter.com/)
- [Twig Templates](https://twig.symfony.com/)

## 🤝 Contributing

When developing new features:

1. Create a feature branch
2. Test locally first
3. Document changes
4. Export database changes if any

## 📧 Support

For OpenCart specific issues: https://opencartforum.com/

---

**Version**: 3.0.3.7 (ocStore)  
**Last Updated**: December 2025

