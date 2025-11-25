# ShopSphere - Local Development Setup

This project runs completely locally with no Azure cloud dependencies.

## Architecture

**Local Components:**
1. **PHP Web Application** - Port 8000
2. **MySQL Database** - Port 3306 (shopsphere_db)
3. **Python Azure Functions** (running locally):
   - User Authentication API - Port 7071
   - Wishlist APIs - Port 7072
   - Payment Processing API - Port 7073
   - Image Upload API - Port 7074

## Quick Start

### 1. Prerequisites
- PHP 8.5+ installed (`C:\php8.5`)
- MySQL Server running locally
- Azure Functions Core Tools v4
- Python 3.9+

### 2. Database Setup
```sql
CREATE DATABASE shopsphere_db;
USE shopsphere_db;
```

Run setup scripts in order:
```powershell
mysql -u root -ppassword shopsphere_db < setup_shopusers.sql
mysql -u root -ppassword shopsphere_db < setup_watches.sql
mysql -u root -ppassword shopsphere_db < setup_wishlist.sql
mysql -u root -ppassword shopsphere_db < setup_cart.sql
mysql -u root -ppassword shopsphere_db < setup_orders.sql
```

### 3. Configuration

**Database credentials** (configured in multiple places):
- `db_config.php` - PHP connection
- `wishlist/local.settings.json` - Wishlist API
- `user_authentication/local.settings.json` - Auth API

All use:
- Host: `localhost`
- Database: `shopsphere_db`
- User: `root`
- Password: `password`

### 4. Start Everything

**Option 1 - Start All Services:**
```powershell
.\start_all.ps1
```
Opens 5 windows: PHP server + 4 function apps

**Option 2 - Start Individually:**
```powershell
# Web app only
.\start_webapp.ps1

# APIs only
.\start_functions.ps1
```

## URLs

**Web Application:**
- Home: http://localhost:8000
- Login: http://localhost:8000/login.php
- Register: http://localhost:8000/register.php
- Catalog: http://localhost:8000/catalog.php
- Cart: http://localhost:8000/cart.php
- Wishlist: http://localhost:8000/wishlist.php
- Orders: http://localhost:8000/my_orders.php
- Admin: http://localhost:8000/admin_dashboard.php

**Local APIs:**
- Login: http://localhost:7071/api/login
- Get Wishlist: http://localhost:7072/api/get_wishlist?user_id=X
- Add to Wishlist: http://localhost:7072/api/add_to_wishlist
- Remove from Wishlist: http://localhost:7072/api/remove_from_wishlist
- Process Payment: http://localhost:7073/api/process_payment
- Upload Image: http://localhost:7074/api/upload_image

## Admin Access

Register with email: `admin@gmail.com` to get admin privileges automatically.

## File Structure

```
myshop/
├── db_config.php                  # MySQL connection config
├── start_all.ps1                  # Start everything
├── start_webapp.ps1               # Start PHP server only
├── start_functions.ps1            # Start all APIs
├── local.settings.json            # Root config (not used directly)
│
├── PHP Web Application Files
├── *.php                          # Web pages
│
├── SQL Setup Files
├── setup_*.sql                    # Database schemas
│
├── user_authentication/           # Login API (Port 7071)
│   ├── local.settings.json
│   ├── host.json
│   └── login/__init__.py
│
├── wishlist/                      # Wishlist APIs (Port 7072)
│   ├── local.settings.json
│   ├── host.json
│   ├── add_to_wishlist/__init__.py
│   ├── get_wishlist/__init__.py
│   └── remove_from_wishlist/__init__.py
│
├── payments/                      # Payment API (Port 7073)
│   ├── local.settings.json
│   ├── host.json
│   └── process_payment/__init__.py
│
└── image_upload/                  # Image Upload API (Port 7074)
    ├── local.settings.json
    ├── host.json
    └── upload_image/__init__.py
```

## Technology Stack

**Frontend:**
- HTML/CSS/JavaScript
- Vanilla JS (no frameworks)
- Dark luxury theme

**Backend:**
- PHP 8.5 (PDO for database)
- Python Azure Functions (running locally)
- MySQL 8.0

**APIs:**
- RESTful JSON APIs
- CORS enabled for local development
- pymysql for database connections

## Local-Only Features

✅ No Azure cloud services required
✅ No external API dependencies
✅ All data stored in local MySQL
✅ All APIs run on localhost
✅ Payment processing is simulated
✅ Image upload can use local storage (Azure Blob Storage optional)

## Development Workflow

1. Start MySQL service
2. Run `.\start_all.ps1`
3. Access http://localhost:8000
4. Make code changes
5. Restart relevant service (PHP or specific function)

## Notes

- PHP extensions required: `curl`, `pdo_mysql`, `mbstring`
- Python packages required: `pymysql`, `azure-functions`
- MySQL root password: `password` (change in all config files if different)
- All functions use `--cors '*'` for local development
- No deployment to Azure needed - pure local development environment

## Troubleshooting

**Can't connect to MySQL:**
```powershell
Get-Service MySQL* | Start-Service
```

**PHP not found:**
- Add `C:\php8.5` to PATH
- Restart terminal

**Function CORS errors:**
- Ensure functions started with `--cors '*'` flag
- Restart functions: `.\start_functions.ps1`

**Database connection fails:**
- Check password in `db_config.php` and `local.settings.json` files
- Verify MySQL is running
- Test: `mysql -u root -ppassword shopsphere_db`
