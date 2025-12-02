#!/bin/bash

# OpenCart Local Development Setup Script
# Usage: ./setup.sh [mode]
# Modes: native, docker

set -e

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

MODE="${1:-native}"

echo "🚀 Starting lukasik.ua Local Setup (Mode: $MODE)"
echo "================================================"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Functions
print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

check_command() {
    if command -v $1 &> /dev/null; then
        print_success "$1 is installed"
        return 0
    else
        print_error "$1 is not installed"
        return 1
    fi
}

# Step 1: Check prerequisites
echo ""
echo "Step 1: Checking prerequisites..."
echo "================================="

if [ "$MODE" = "docker" ]; then
    check_command docker || { print_error "Docker is required. Install from https://www.docker.com/"; exit 1; }
    check_command docker-compose || { print_error "Docker Compose is required"; exit 1; }
else
    check_command php || { print_error "PHP 7.3+ is required"; exit 1; }
    check_command mysql || print_warning "MySQL not found in PATH, but may be running"
fi

# Step 2: Create storage directories
echo ""
echo "Step 2: Creating storage directories..."
echo "======================================="

mkdir -p storage/{cache,download,logs,modification,session,upload}
print_success "Storage directories created"

# Step 3: Set permissions
echo ""
echo "Step 3: Setting permissions..."
echo "=============================="

chmod -R 755 storage/
chmod -R 755 image/ 2>/dev/null || print_warning "Could not set image/ permissions"

print_success "Permissions set"

# Step 4: Configuration
echo ""
echo "Step 4: Setting up configuration..."
echo "==================================="

if [ "$MODE" = "docker" ]; then
    # Copy Docker config files
    if [ ! -f "config.php" ]; then
        cp config-docker.php config.php
        print_success "Frontend config.php created (Docker)"
    else
        print_warning "config.php already exists, skipping"
    fi
    
    if [ ! -f "admin/config.php" ]; then
        cp admin/config-docker.php admin/config.php
        print_success "Admin config.php created (Docker)"
    else
        print_warning "admin/config.php already exists, skipping"
    fi
else
    # Use local config
    if [ ! -f "config.php" ]; then
        print_success "Frontend config.php already created"
    fi
    
    if [ ! -f "admin/config.php" ]; then
        if [ -f "admin/config-local.php" ]; then
            cp admin/config-local.php admin/config.php
            print_success "Admin config.php created (Local)"
        else
            print_warning "admin/config.php not found, you'll need to create it"
        fi
    else
        print_warning "admin/config.php already exists"
    fi
fi

# Step 5: Start services
echo ""
echo "Step 5: Starting services..."
echo "============================"

if [ "$MODE" = "docker" ]; then
    print_success "Starting Docker containers..."
    docker-compose up -d
    
    echo ""
    print_success "Docker containers started!"
    echo ""
    echo "Services running:"
    echo "  - Web:        http://localhost:8000"
    echo "  - Admin:      http://localhost:8000/admin"
    echo "  - phpMyAdmin: http://localhost:8080"
    echo "  - MySQL:      localhost:3306"
    echo "  - Redis:      localhost:6379"
    echo ""
    echo "Database credentials:"
    echo "  - Username: opencart"
    echo "  - Password: opencart"
    echo "  - Database: lukasik_local"
    echo ""
    print_warning "Waiting for database to initialize (this may take 2-3 minutes for first import)..."
    echo ""
    echo "Monitor progress with: docker-compose logs -f mysql"
    echo ""
    echo "Check if ready with: docker-compose ps"
    
else
    echo ""
    print_success "Setup complete! Now you need to:"
    echo ""
    echo "1. Start MySQL if not running:"
    echo "   brew services start mysql"
    echo ""
    echo "2. Create database and import data:"
    echo "   mysql -u root -p -e \"CREATE DATABASE lukasik_local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\""
    echo "   mysql -u root -p lukasik_local < database_backup_2025-11-26_23-08-12.sql"
    echo ""
    echo "3. Update database URLs:"
    echo "   mysql -u root -p lukasik_local -e \"UPDATE oc_setting SET value = 'http://localhost:8000/' WHERE \\\`key\\\` = 'config_url';\""
    echo ""
    echo "4. Start PHP development server:"
    echo "   php -S localhost:8000"
    echo ""
    echo "5. Access the site:"
    echo "   - Frontend: http://localhost:8000"
    echo "   - Admin:    http://localhost:8000/admin"
    echo ""
fi

# Step 6: Post-setup instructions
echo ""
echo "📋 Next Steps:"
echo "============="
echo ""

if [ "$MODE" = "docker" ]; then
    echo "Once database is ready (check with: docker-compose ps):"
    echo ""
    echo "1. Update database URLs (run this in your terminal):"
    echo "   docker-compose exec mysql mysql -u opencart -popencart lukasik_local -e \\"
    echo "     \"UPDATE oc_setting SET value = 'http://localhost:8000/' WHERE \\\`key\\\` = 'config_url';\""
    echo ""
fi

echo "2. Get admin credentials from database:"
echo "   SELECT username, email FROM oc_user;"
echo ""
echo "3. Reset admin password if needed:"
echo "   UPDATE oc_user SET password = MD5('admin') WHERE user_id = 1;"
echo ""
echo "4. Clear cache:"
echo "   rm -rf storage/cache/*"
echo ""
echo "5. Read full documentation: SETUP_LOCAL.md"
echo ""

print_success "Setup complete! 🎉"
echo ""

if [ "$MODE" = "docker" ]; then
    echo "Useful Docker commands:"
    echo "  docker-compose ps              # Check status"
    echo "  docker-compose logs -f         # View logs"
    echo "  docker-compose stop            # Stop containers"
    echo "  docker-compose down            # Stop and remove containers"
    echo "  docker-compose down -v         # Stop and remove containers + volumes"
fi

echo ""

