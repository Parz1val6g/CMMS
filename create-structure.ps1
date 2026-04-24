# ==============================================
# CREATE LARAVEL PROJECT STRUCTURE
# ==============================================

Write-Host "Creating app/Features structure..." -ForegroundColor Green

$features = @(
    "Authentication", "Clients", "ServiceOrders", "Tasks", "MiniTasks", 
    "WorkLogs", "Sectors", "Teams", "Workers", "Materials", 
    "Locations", "ServiceTypes", "Admin", "Export", "Settings", "Notifications"
)

foreach ($feature in $features) {
    # Controllers
    New-Item -ItemType Directory -Force -Path "app/Features/$feature/Controllers" | Out-Null
    
    # Services
    New-Item -ItemType Directory -Force -Path "app/Features/$feature/Services" | Out-Null
    
    # Models (skip for some features)
    if ($feature -notin @("Admin", "Export", "Settings", "Notifications")) {
        New-Item -ItemType Directory -Force -Path "app/Features/$feature/Models" | Out-Null
    }
    
    # Policies
    New-Item -ItemType Directory -Force -Path "app/Features/$feature/Policies" | Out-Null
    
    # Requests
    New-Item -ItemType Directory -Force -Path "app/Features/$feature/Requests" | Out-Null
    
    # Resources
    New-Item -ItemType Directory -Force -Path "app/Features/$feature/Resources" | Out-Null
    
    # Routes
    New-Item -ItemType Directory -Force -Path "app/Features/$feature/Routes" | Out-Null
    
    # Factories
    New-Item -ItemType Directory -Force -Path "app/Features/$feature/Factories" | Out-Null
    
    # Tests
    New-Item -ItemType Directory -Force -Path "app/Features/$feature/Tests/Feature" | Out-Null
    New-Item -ItemType Directory -Force -Path "app/Features/$feature/Tests/Unit" | Out-Null
    
    Write-Host "[OK] Created $feature feature"
}

Write-Host "Creating app/Core structure..." -ForegroundColor Green
$coreDirs = @("Services", "Traits", "Enums", "Helpers", "Policies", "Middleware")
foreach ($dir in $coreDirs) {
    New-Item -ItemType Directory -Force -Path "app/Core/$dir" | Out-Null
}
Write-Host "[OK] Created app/Core"

Write-Host "Creating app/Shared structure..." -ForegroundColor Green
New-Item -ItemType Directory -Force -Path "app/Shared/Models" | Out-Null
New-Item -ItemType Directory -Force -Path "app/Shared/Services" | Out-Null
Write-Host "[OK] Created app/Shared"

Write-Host "Creating routes/api structure..." -ForegroundColor Green
New-Item -ItemType Directory -Force -Path "routes/api" | Out-Null
Write-Host "[OK] Created routes/api"

Write-Host "Creating database structure..." -ForegroundColor Green
New-Item -ItemType Directory -Force -Path "database/factories" | Out-Null
New-Item -ItemType Directory -Force -Path "database/seeders" | Out-Null
New-Item -ItemType Directory -Force -Path "database/migrations" | Out-Null
Write-Host "[OK] Created database folders"

Write-Host "Creating resources/js structure..." -ForegroundColor Green
$jsFeatures = @(
    "Authentication", "Clients", "Tasks", "MiniTasks", "WorkLogs", 
    "Sectors", "Teams", "Workers", "Materials", "ServiceOrders", 
    "Locations", "ServiceTypes", "Admin", "Dashboard"
)

foreach ($feature in $jsFeatures) {
    New-Item -ItemType Directory -Force -Path "resources/js/Features/$feature/Pages" | Out-Null
    New-Item -ItemType Directory -Force -Path "resources/js/Features/$feature/Components" | Out-Null
    New-Item -ItemType Directory -Force -Path "resources/js/Features/$feature/composables" | Out-Null
}
Write-Host "[OK] Created resources/js/Features"

New-Item -ItemType Directory -Force -Path "resources/js/Components/Common" | Out-Null
Write-Host "[OK] Created resources/js/Components/Common"

New-Item -ItemType Directory -Force -Path "resources/js/composables" | Out-Null
New-Item -ItemType Directory -Force -Path "resources/js/services/api" | Out-Null
New-Item -ItemType Directory -Force -Path "resources/js/stores" | Out-Null
New-Item -ItemType Directory -Force -Path "resources/js/utils" | Out-Null
Write-Host "[OK] Created resources/js utilities"

Write-Host "Creating tests structure..." -ForegroundColor Green
$testFeatures = @(
    "Authentication", "Clients", "ServiceOrders", "Tasks", "MiniTasks", 
    "WorkLogs", "Sectors", "Teams", "Workers", "Materials", 
    "Locations", "Admin"
)

foreach ($feature in $testFeatures) {
    New-Item -ItemType Directory -Force -Path "tests/Feature/$feature" | Out-Null
    New-Item -ItemType Directory -Force -Path "tests/Unit/$feature" | Out-Null
}

New-Item -ItemType Directory -Force -Path "tests/Unit/Services" | Out-Null
New-Item -ItemType Directory -Force -Path "tests/Unit/Policies" | Out-Null
New-Item -ItemType Directory -Force -Path "tests/Unit/Helpers" | Out-Null
New-Item -ItemType Directory -Force -Path "tests/Unit/Traits" | Out-Null
Write-Host "[OK] Created tests structure"

Write-Host ""
Write-Host "[SUCCESS] Project structure created successfully!" -ForegroundColor Green
