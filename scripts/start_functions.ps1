# Start all Azure Functions locally for ShopSphere
Write-Host "Starting ShopSphere Azure Functions..." -ForegroundColor Green
Write-Host ""

# Check if func command exists
if (!(Get-Command func -ErrorAction SilentlyContinue)) {
    Write-Host "ERROR: Azure Functions Core Tools not found!" -ForegroundColor Red
    Write-Host "Install it with: npm install -g azure-functions-core-tools@4 --unsafe-perm true" -ForegroundColor Yellow
    exit 1
}

Write-Host "Database Configuration:" -ForegroundColor Cyan
Write-Host "  Host: localhost" -ForegroundColor Gray
Write-Host "  Database: shopsphere_db" -ForegroundColor Gray
Write-Host "  Port: 3306" -ForegroundColor Gray
Write-Host ""

$functions = @(
    @{Path="user_authentication"; Port=7071; Name="User Authentication"},
    @{Path="wishlist"; Port=7072; Name="Wishlist APIs"},
    @{Path="payments"; Port=7073; Name="Payment Processing"},
    @{Path="image_upload"; Port=7074; Name="Image Upload"}
)

Write-Host "Starting 4 Function Apps in separate windows:" -ForegroundColor Cyan
Write-Host ""

# Get parent directory (myshop root)
$rootDir = Split-Path -Parent $PSScriptRoot

foreach ($func in $functions) {
    $fullPath = Join-Path $rootDir $func.Path
    if (Test-Path $fullPath) {
        Write-Host "  Starting $($func.Name) on port $($func.Port)..." -ForegroundColor Gray
        
        # Start each function in a new PowerShell window with CORS enabled
        Start-Process powershell -ArgumentList @(
            "-NoExit",
            "-Command",
            "cd '$fullPath'; Write-Host '$($func.Name) - Port $($func.Port)' -ForegroundColor Green; Write-Host ''; func start --port $($func.Port) --cors '*'"
        )
        
        Start-Sleep -Seconds 1
    } else {
        Write-Host "  Warning: $($func.Path) not found at $fullPath, skipping..." -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "All function windows opened!" -ForegroundColor Green
Write-Host ""
Write-Host "Available Endpoints:" -ForegroundColor Yellow
Write-Host "  User Authentication:" -ForegroundColor Cyan
Write-Host "    http://localhost:7071/api/login" -ForegroundColor White
Write-Host ""
Write-Host "  Wishlist APIs:" -ForegroundColor Cyan
Write-Host "    http://localhost:7072/api/add_to_wishlist" -ForegroundColor White
Write-Host "    http://localhost:7072/api/get_wishlist" -ForegroundColor White
Write-Host "    http://localhost:7072/api/remove_from_wishlist" -ForegroundColor White
Write-Host ""
Write-Host "  Payment Processing:" -ForegroundColor Cyan
Write-Host "    http://localhost:7073/api/process_payment" -ForegroundColor White
Write-Host ""
Write-Host "  Image Upload:" -ForegroundColor Cyan
Write-Host "    http://localhost:7074/api/upload_image" -ForegroundColor White
Write-Host ""
Write-Host "Close each window to stop the respective function." -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Green
