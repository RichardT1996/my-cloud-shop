# Start Complete ShopSphere Application (Web App + Azure Functions)
Write-Host "========================================" -ForegroundColor Green
Write-Host "  ShopSphere - Full Stack Startup" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""

# Check prerequisites
Write-Host "Checking prerequisites..." -ForegroundColor Cyan

$errors = @()

# Check PHP
if (!(Get-Command php -ErrorAction SilentlyContinue)) {
    $errors += "PHP not found"
} else {
    Write-Host "  [OK] PHP installed" -ForegroundColor Green
}

# Check Azure Functions Core Tools
if (!(Get-Command func -ErrorAction SilentlyContinue)) {
    $errors += "Azure Functions Core Tools not found"
} else {
    Write-Host "  [OK] Azure Functions Core Tools installed" -ForegroundColor Green
}

# Check MySQL
$mysqlRunning = Get-Service MySQL* -ErrorAction SilentlyContinue | Where-Object { $_.Status -eq 'Running' }
if (!$mysqlRunning) {
    $errors += "MySQL not running"
} else {
    Write-Host "  [OK] MySQL is running" -ForegroundColor Green
}

if ($errors.Count -gt 0) {
    Write-Host ""
    Write-Host "ERRORS found:" -ForegroundColor Red
    foreach ($err in $errors) {
        Write-Host "  - $err" -ForegroundColor Red
    }
    Write-Host ""
    Write-Host "Please fix the errors above before continuing." -ForegroundColor Yellow
    exit 1
}

Write-Host ""
Write-Host "All prerequisites OK!" -ForegroundColor Green
Write-Host ""

# Start Azure Functions in separate windows
Write-Host "Starting Azure Functions..." -ForegroundColor Cyan
& "$PSScriptRoot\start_functions.ps1"

Start-Sleep -Seconds 2

# Start PHP Web Server in a new window
Write-Host ""
Write-Host "Starting PHP Web Application..." -ForegroundColor Cyan
$rootDir = Split-Path -Parent $PSScriptRoot
Start-Process powershell -ArgumentList @(
    "-NoExit",
    "-Command",
    "cd '$rootDir'; Write-Host 'ShopSphere Web Application' -ForegroundColor Green; Write-Host 'URL: http://localhost:8000' -ForegroundColor Cyan; Write-Host ''; php -S localhost:8000 -t '$rootDir'"
)

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "All services started!" -ForegroundColor Green
Write-Host ""
Write-Host "Access your application:" -ForegroundColor Yellow
Write-Host "  Web App:        http://localhost:8000" -ForegroundColor White
Write-Host "  Login API:      http://localhost:7071/api/login" -ForegroundColor White
Write-Host "  Wishlist API:   http://localhost:7072/api/*" -ForegroundColor White
Write-Host "  Payment API:    http://localhost:7073/api/process_payment" -ForegroundColor White
Write-Host "  Image Upload:   http://localhost:7074/api/upload_image" -ForegroundColor White
Write-Host ""
Write-Host "Close windows to stop services." -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Green
