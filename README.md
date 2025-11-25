# ShopSphere - Luxury Watch E-Commerce Platform

A full-stack e-commerce application for luxury watches, built with PHP, MySQL, and Azure Functions (running locally).

## 🚀 Quick Start

```powershell
# Start all services (PHP web app + Azure Functions)
cd scripts
.\start_all.ps1
```

Access the application at: **http://localhost:8000**

## 📁 Project Structure

```
myshop/
├── php/                    # Web application pages
│   ├── index.php          # Home page
│   ├── catalog.php        # Product catalog
│   ├── cart.php           # Shopping cart
│   ├── checkout.php       # Checkout process
│   ├── wishlist.php       # User wishlist
│   ├── my_orders.php      # Order history
│   ├── login.php          # User login
│   ├── register.php       # User registration
│   └── ...
├── admin/                  # Admin dashboard pages
│   ├── admin_dashboard.php    # Product management
│   ├── admin_orders.php       # Order management
│   ├── view_users.php         # User management
│   └── admin_process.php      # Admin actions handler
├── api/                    # PHP API endpoints
│   ├── get_order_details.php
│   ├── get_admin_order_details.php
│   ├── update_order_status.php
│   └── cancel_order.php
├── user_authentication/    # Azure Function - User login API
├── wishlist/              # Azure Functions - Wishlist APIs
├── payments/              # Azure Function - Payment processing
├── image_upload/          # Azure Function - Image upload (optional)
├── sql/                   # Database setup scripts
│   ├── setup_shopusers.sql
│   ├── setup_watches.sql
│   ├── setup_wishlist.sql
│   ├── setup_cart.sql
│   └── setup_orders.sql
├── scripts/               # Startup scripts
│   ├── start_all.ps1      # Start everything
│   ├── start_functions.ps1 # Start Azure Functions only
│   └── start_webapp.ps1    # Start PHP web app only
├── docs/                  # Documentation
│   └── LOCAL_SETUP.md     # Detailed setup guide
├── images/                # Product images
├── lib/                   # JavaScript libraries
├── db_config.php          # Database connection configuration
└── .gitignore
```

## 🛠️ Tech Stack

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP 8.5
- **Database**: MySQL 8.x
- **API Layer**: Azure Functions (Python 3.9+) running locally
- **Server**: PHP Built-in Development Server

## 📋 Prerequisites

1. **PHP 8.5+** with extensions:
   - pdo
   - pdo_mysql
   - curl
   - mysqli
   - mbstring
   - openssl

2. **MySQL 8.x** running on localhost:3306

3. **Azure Functions Core Tools v4**
   ```powershell
   npm install -g azure-functions-core-tools@4 --unsafe-perm true
   ```

4. **Python 3.9+** with pip

## 🔧 Installation

### 1. Clone the Repository
```powershell
git clone <repository-url>
cd myshop
```

### 2. Install Python Dependencies
```powershell
pip install pymysql azure-functions
```

### 3. Setup MySQL Database
```powershell
# Connect to MySQL
mysql -u root -p

# Create database
CREATE DATABASE shopsphere_db;
USE shopsphere_db;

# Run setup scripts
source sql/setup_shopusers.sql
source sql/setup_watches.sql
source sql/setup_wishlist.sql
source sql/setup_cart.sql
source sql/setup_orders.sql
```

### 4. Configure Database Connection
The `db_config.php` file contains the database connection settings:
```php
$host = 'localhost';
$dbname = 'shopsphere_db';
$username = 'root';
$password = 'password';
```

Update if your MySQL credentials are different.

### 5. Configure Azure Functions
Each function folder has a `local.settings.json` file. Verify the database settings:
```json
{
  "Values": {
    "DB_HOST": "localhost",
    "DB_NAME": "shopsphere_db",
    "DB_USER": "root",
    "DB_PASS": "password",
    "DB_PORT": "3306"
  }
}
```

## 🚀 Running the Application

### Option 1: Start Everything at Once
```powershell
cd scripts
.\start_all.ps1
```
This will open 5 PowerShell windows:
- Window 1: User Authentication API (port 7071)
- Window 2: Wishlist APIs (port 7072)
- Window 3: Payment API (port 7073)
- Window 4: Image Upload API (port 7074)
- Window 5: PHP Web Application (port 8000)

### Option 2: Start Services Separately
```powershell
# Start Azure Functions
cd scripts
.\start_functions.ps1

# Start PHP web app (in a new terminal)
cd scripts
.\start_webapp.ps1
```

## 🌐 Available Endpoints

### Web Application
- **Home**: http://localhost:8000
- **Catalog**: http://localhost:8000/catalog.php
- **Login**: http://localhost:8000/login.php
- **Register**: http://localhost:8000/register.php
- **Admin Dashboard**: http://localhost:8000/../admin/admin_dashboard.php

### API Endpoints
- **Login**: http://localhost:7071/api/login (POST)
- **Add to Wishlist**: http://localhost:7072/api/add_to_wishlist (POST)
- **Get Wishlist**: http://localhost:7072/api/get_wishlist (GET)
- **Remove from Wishlist**: http://localhost:7072/api/remove_from_wishlist (POST)
- **Process Payment**: http://localhost:7073/api/process_payment (POST)

## 👤 Default Admin Account

After running the setup scripts, create an admin account:
- Email: admin@gmail.com
- Password: (set during registration)
- Set `is_admin = 1` in the database:
  ```sql
  UPDATE shopusers SET is_admin = 1 WHERE email = 'admin@gmail.com';
  ```

## 📚 Features

### For Customers
- ✅ Browse luxury watch catalog
- ✅ Add products to wishlist
- ✅ Shopping cart management
- ✅ Secure checkout process
- ✅ Order tracking
- ✅ Order history
- ✅ Virtual payment processing

### For Administrators
- ✅ Product management (Add, Edit, Delete)
- ✅ Order management (View, Update Status)
- ✅ User management
- ✅ Dashboard analytics
- ✅ Real-time order updates

## 🐛 Troubleshooting

### PHP Errors
```powershell
# Check PHP version
php -v

# Check enabled extensions
php -m
```

### MySQL Connection Issues
```powershell
# Check if MySQL is running
Get-Service MySQL* | Select-Object Name, Status

# Test connection
mysql -u root -p shopsphere_db
```

### Azure Functions Not Starting
```powershell
# Check if func is installed
func --version

# Check Python version
python --version

# Install dependencies
pip install pymysql azure-functions
```

### CORS Errors
All functions are started with `--cors '*'` flag. If you still see CORS errors, check browser console for details.

## 📄 License

This project is for educational purposes.

## 🤝 Contributing

This is a university project. Contributions are not currently accepted.
