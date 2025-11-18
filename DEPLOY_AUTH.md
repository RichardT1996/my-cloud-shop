# Deploy User Authentication Function App

## What This Does
When users log in, your PHP site will call the Azure Function App to verify credentials against your database.

---

## Step 1: Deploy the Function App

Open PowerShell and run:

```powershell
cd "c:\Users\Richa\Documents\Uni Stuff\myshop\user_authentication"
func azure functionapp publish user-authentication-agddcraseuh4dtf0
```

This will take 2-3 minutes. Wait for "Deployment successful" message.

---

## Step 2: Configure Database Connection

The Function App needs to know how to connect to your database.

### Option A: Azure Portal (Recommended for first time)

1. Go to https://portal.azure.com
2. Search for `user-authentication-agddcraseuh4dtf0` and open it
3. Click **Configuration** in the left menu (under Settings)
4. Click **+ New application setting** and add each of these:

| Name | Value |
|------|-------|
| `DB_SERVER` | `tcp:mycardiffmet1.database.windows.net,1433` |
| `DB_NAME` | `myDatabase` |
| `DB_USER` | `myadmin` |
| `DB_PASS` | `password123!` |

5. Click **Save** at the top
6. Click **Continue** when it asks to restart

### Option B: Azure CLI (if you prefer command line)

First, find your resource group name:
```powershell
az functionapp list --query "[?name=='user-authentication-agddcraseuh4dtf0'].resourceGroup" -o tsv
```

Then set the configuration (replace `<RESOURCE_GROUP>` with the result above):
```powershell
az functionapp config appsettings set `
  --name user-authentication-agddcraseuh4dtf0 `
  --resource-group <RESOURCE_GROUP> `
  --settings `
    DB_SERVER="tcp:mycardiffmet1.database.windows.net,1433" `
    DB_NAME="myDatabase" `
    DB_USER="myadmin" `
    DB_PASS="password123!"
```

---

## Step 3: Enable CORS (Allow PHP Site to Call the API)

### In Azure Portal:
1. Still in your Function App
2. Click **CORS** in the left menu (under API section)
3. Add these allowed origins:
   - `http://localhost`
   - `http://localhost:8080`
   - `*` (for testing - remove this in production!)
4. Click **Save**

### Or via CLI:
```powershell
az functionapp cors add `
  --name user-authentication-agddcraseuh4dtf0 `
  --resource-group <RESOURCE_GROUP> `
  --allowed-origins "http://localhost" "http://localhost:8080" "*"
```

---

## Step 4: Test the Function App

Run this test to verify it's working:

```powershell
# Test with a user that doesn't exist (should return 401)
$testBody = @{
    email = "nonexistent@test.com"
    password = "test"
} | ConvertTo-Json

try {
    Invoke-RestMethod `
      -Uri "https://user-authentication-agddcraseuh4dtf0.norwayeast-01.azurewebsites.net/api/login" `
      -Method POST `
      -Body $testBody `
      -ContentType "application/json"
} catch {
    Write-Host "Response: $($_.Exception.Response.StatusCode) - This is expected for invalid user"
    Write-Host $_.ErrorDetails.Message
}
```

**Expected**: You should see a 401 error with "Invalid credentials" - this is GOOD! It means the Function App is connecting to your database.

---

## Step 5: Test with Real User

Replace with actual credentials from your `shopusers` table:

```powershell
$loginBody = @{
    email = "your-email@example.com"
    password = "your-password"
} | ConvertTo-Json

$response = Invoke-RestMethod `
  -Uri "https://user-authentication-agddcraseuh4dtf0.norwayeast-01.azurewebsites.net/api/login" `
  -Method POST `
  -Body $loginBody `
  -ContentType "application/json"

# Show response
$response | ConvertTo-Json -Depth 5
```

**Expected success response:**
```json
{
  "success": true,
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "your-email@example.com",
    "is_admin": false
  },
  "hashed_password": "$2y$10$..."
}
```

---

## Step 6: Test Your Website

1. Open your PHP site in a browser
2. Go to the login page
3. Try logging in with valid credentials
4. Check browser Developer Tools (F12) → Console for any errors

Your login now uses the Azure Function App! If the API is down, it will automatically fall back to direct database connection.

---

## Troubleshooting

### "Could not find Azure Functions runtime"
```powershell
winget install Microsoft.Azure.FunctionsCoreTools
```

### "Failed to connect to Azure"
```powershell
az login
```

### Function returns 500 error
- Check logs in Azure Portal: Function App → Monitor → Log stream
- Verify all environment variables are set correctly
- Check if Azure SQL firewall allows Azure services

### "ODBC Driver 18 not found"
The Function App runtime should have this, but if you see this error:
- Check Application Insights logs in Azure Portal
- May need to wait a few minutes for dependencies to install after first deployment

### Login still uses local database
- Check browser Console (F12) for API errors
- Verify CORS is configured
- Test the Function URL directly (Step 4 above)
- Check PHP error logs for curl errors

---

## Quick Command Reference

```powershell
# Deploy
cd "c:\Users\Richa\Documents\Uni Stuff\myshop\user_authentication"
func azure functionapp publish user-authentication-agddcraseuh4dtf0

# Test
$body = @{ email = "test@test.com"; password = "test" } | ConvertTo-Json
Invoke-RestMethod -Uri "https://user-authentication-agddcraseuh4dtf0.norwayeast-01.azurewebsites.net/api/login" -Method POST -Body $body -ContentType "application/json"

# View logs
az functionapp log tail --name user-authentication-agddcraseuh4dtf0 --resource-group <RESOURCE_GROUP>
```
