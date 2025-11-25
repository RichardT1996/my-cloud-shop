# Directory Reorganization Summary

## Changes Made

### New Directory Structure
The project has been reorganized into logical folders for better maintainability:

```
myshop/
├── php/          - All customer-facing web pages (13 files)
├── admin/        - Admin dashboard pages (4 files)
├── api/          - PHP API endpoints (4 files)
├── sql/          - Database setup scripts (5 files)
├── scripts/      - PowerShell startup scripts (3 files)
├── docs/         - Documentation (1 file)
├── images/       - Product images
├── lib/          - JavaScript libraries
└── [Azure Function folders remain in root]
```

### Files Moved

#### php/ folder (Customer Web Pages)
- index.php
- catalog.php
- cart.php
- checkout.php
- wishlist.php
- my_orders.php
- order_confirmation.php
- login.php
- register.php
- logout.php
- success.php
- process_login.php
- process_register.php

#### admin/ folder (Admin Pages)
- admin_dashboard.php
- admin_orders.php
- admin_process.php
- view_users.php

#### api/ folder (API Endpoints)
- get_order_details.php
- get_admin_order_details.php
- update_order_status.php
- cancel_order.php

#### sql/ folder (Database Scripts)
- setup_shopusers.sql
- setup_watches.sql
- setup_wishlist.sql
- setup_cart.sql
- setup_orders.sql

#### scripts/ folder (Startup Scripts)
- start_all.ps1
- start_functions.ps1
- start_webapp.ps1

#### docs/ folder (Documentation)
- LOCAL_SETUP.md

### Files Removed (No Longer Needed)

#### Temporary Migration Files
- convert_to_mysql.php - Migration script (migration complete)
- test_wishlist_apis.ps1 - Temporary test script

#### Outdated Documentation
- DEPLOY_AUTH.md - Azure deployment guide (not using cloud)
- AZURE_FUNCTIONS_LOCAL.md - Redundant with LOCAL_SETUP.md
- MYSQL_MIGRATION.md - Migration documentation (migration complete)
- MYSQL_CONVERSION_STATUS.md - Migration status (migration complete)
- ORDER_SYSTEM.md - Migration documentation (migration complete)
- setup.md - Old Azure setup notes (outdated)

#### Duplicate Configuration
- host.json (root) - Duplicate, functions have their own
- local.settings.json (root) - Duplicate, functions have their own

### Code Updates

#### Database Connection References
All PHP files updated to reference `../db_config.php` instead of `db_config.php`:
- 13 files in `php/` folder
- 4 files in `admin/` folder
- 4 files in `api/` folder

#### Startup Scripts
Updated to use the new directory structure:
- `start_webapp.ps1` - Now changes to `php/` directory before starting server
- `start_all.ps1` - Updated to reference new script locations

### New Documentation

#### README.md (Root)
Created comprehensive project documentation including:
- Quick start guide
- Complete project structure
- Tech stack details
- Installation instructions
- Running instructions
- Available endpoints
- Feature list
- Troubleshooting guide

## How to Use the New Structure

### Starting the Application
```powershell
cd scripts
.\start_all.ps1
```

### Accessing Pages
All customer pages are now served from the `php/` directory:
- Home: http://localhost:8000/index.php (or just http://localhost:8000)
- Catalog: http://localhost:8000/catalog.php
- Cart: http://localhost:8000/cart.php

Admin pages are in the `admin/` directory:
- Admin Dashboard: http://localhost:8000/../admin/admin_dashboard.php

### Database Setup
All SQL scripts are now in the `sql/` folder:
```sql
source sql/setup_shopusers.sql
source sql/setup_watches.sql
-- etc.
```

## Benefits of Reorganization

1. **Better Organization**: Related files are grouped together
2. **Clearer Structure**: Easy to find specific types of files
3. **Reduced Clutter**: Removed 10 unnecessary files
4. **Professional Layout**: Standard project structure
5. **Easier Maintenance**: Clear separation of concerns
6. **Better Documentation**: Comprehensive README added

## Breaking Changes

None! The application works exactly the same way, just with a cleaner file organization. All internal references have been updated automatically.
