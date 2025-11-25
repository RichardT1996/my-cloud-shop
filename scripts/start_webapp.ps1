# Start ShopSphere Web Application Locally
Write-Host "Starting ShopSphere Web Application..." -ForegroundColor Green
Write-Host ""

# Check if PHP is installed
if (!(Get-Command php -ErrorAction SilentlyContinue)) {
    Write-Host "ERROR: PHP not found!" -ForegroundColor Red
    Write-Host "Install PHP or add it to your PATH" -ForegroundColor Yellow
    Write-Host "Download from: https://windows.php.net/download/" -ForegroundColor Cyan
    exit 1
}

# Check PHP version
$phpVersion = php -v | Select-Object -First 1
Write-Host "PHP Version: $phpVersion" -ForegroundColor Cyan
Write-Host ""

# Check if MySQL is running
Write-Host "Checking MySQL connection..." -ForegroundColor Cyan
$mysqlRunning = Get-Service MySQL* -ErrorAction SilentlyContinue | Where-Object { $_.Status -eq 'Running' }
if ($mysqlRunning) {
    Write-Host "  MySQL is running: $($mysqlRunning.DisplayName)" -ForegroundColor Green
} else {
    Write-Host "  WARNING: MySQL service not found or not running!" -ForegroundColor Yellow
    Write-Host "  Make sure MySQL is installed and running" -ForegroundColor Yellow
}
Write-Host ""

Write-Host "Web Application Configuration:" -ForegroundColor Cyan
Write-Host "  Database: shopsphere_db" -ForegroundColor Gray
Write-Host "  PHP Server: http://localhost:8000" -ForegroundColor Gray
Write-Host ""

Write-Host "Starting PHP Development Server..." -ForegroundColor Yellow
Write-Host "Press Ctrl+C to stop the server" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Green
Write-Host ""

# Start PHP dev server with document root set to project root so admin/, api/, images/ are reachable
$rootDir = Split-Path -Parent $PSScriptRoot

# Use -t to set the PHP built-in server document root to the project root
try {
    php -S localhost:8000 -t "$rootDir"
}
catch {
    Write-Host ""
    Write-Host "ERROR: Failed to start PHP server" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    exit 1
}
