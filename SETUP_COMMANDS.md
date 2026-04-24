# Comandos para Criar Projeto Laravel + Estrutura

## 1. Criar Projeto Laravel 12

```powershell
composer create-project laravel/laravel splnet
cd splnet
```

---

## 2. Estrutura app/Features/ (16 Features)

```powershell
# Authentication
mkdir app/Features/Authentication/Controllers
mkdir app/Features/Authentication/Services
mkdir app/Features/Authentication/Requests
mkdir app/Features/Authentication/Routes
mkdir app/Features/Authentication/Factories
mkdir app/Features/Authentication/Tests/Feature
mkdir app/Features/Authentication/Tests/Unit

# Clients
mkdir app/Features/Clients/Controllers
mkdir app/Features/Clients/Services
mkdir app/Features/Clients/Models
mkdir app/Features/Clients/Policies
mkdir app/Features/Clients/Requests
mkdir app/Features/Clients/Resources
mkdir app/Features/Clients/Routes
mkdir app/Features/Clients/Factories
mkdir app/Features/Clients/Tests/Feature
mkdir app/Features/Clients/Tests/Unit

# ServiceOrders
mkdir app/Features/ServiceOrders/Controllers
mkdir app/Features/ServiceOrders/Services
mkdir app/Features/ServiceOrders/Models
mkdir app/Features/ServiceOrders/Policies
mkdir app/Features/ServiceOrders/Requests
mkdir app/Features/ServiceOrders/Resources
mkdir app/Features/ServiceOrders/Routes
mkdir app/Features/ServiceOrders/Factories
mkdir app/Features/ServiceOrders/Tests/Feature
mkdir app/Features/ServiceOrders/Tests/Unit

# Tasks
mkdir app/Features/Tasks/Controllers
mkdir app/Features/Tasks/Services
mkdir app/Features/Tasks/Models
mkdir app/Features/Tasks/Policies
mkdir app/Features/Tasks/Requests
mkdir app/Features/Tasks/Resources
mkdir app/Features/Tasks/Routes
mkdir app/Features/Tasks/Factories
mkdir app/Features/Tasks/Tests/Feature
mkdir app/Features/Tasks/Tests/Unit

# MiniTasks
mkdir app/Features/MiniTasks/Controllers
mkdir app/Features/MiniTasks/Services
mkdir app/Features/MiniTasks/Models
mkdir app/Features/MiniTasks/Policies
mkdir app/Features/MiniTasks/Requests
mkdir app/Features/MiniTasks/Resources
mkdir app/Features/MiniTasks/Routes
mkdir app/Features/MiniTasks/Factories
mkdir app/Features/MiniTasks/Tests/Feature
mkdir app/Features/MiniTasks/Tests/Unit

# WorkLogs
mkdir app/Features/WorkLogs/Controllers
mkdir app/Features/WorkLogs/Services
mkdir app/Features/WorkLogs/Models
mkdir app/Features/WorkLogs/Policies
mkdir app/Features/WorkLogs/Requests
mkdir app/Features/WorkLogs/Resources
mkdir app/Features/WorkLogs/Routes
mkdir app/Features/WorkLogs/Factories
mkdir app/Features/WorkLogs/Tests/Feature
mkdir app/Features/WorkLogs/Tests/Unit

# Sectors
mkdir app/Features/Sectors/Controllers
mkdir app/Features/Sectors/Services
mkdir app/Features/Sectors/Models
mkdir app/Features/Sectors/Policies
mkdir app/Features/Sectors/Requests
mkdir app/Features/Sectors/Resources
mkdir app/Features/Sectors/Routes
mkdir app/Features/Sectors/Factories
mkdir app/Features/Sectors/Tests/Feature
mkdir app/Features/Sectors/Tests/Unit

# Teams
mkdir app/Features/Teams/Controllers
mkdir app/Features/Teams/Services
mkdir app/Features/Teams/Models
mkdir app/Features/Teams/Policies
mkdir app/Features/Teams/Requests
mkdir app/Features/Teams/Resources
mkdir app/Features/Teams/Routes
mkdir app/Features/Teams/Factories
mkdir app/Features/Teams/Tests/Feature
mkdir app/Features/Teams/Tests/Unit

# Workers
mkdir app/Features/Workers/Controllers
mkdir app/Features/Workers/Services
mkdir app/Features/Workers/Models
mkdir app/Features/Workers/Policies
mkdir app/Features/Workers/Requests
mkdir app/Features/Workers/Resources
mkdir app/Features/Workers/Routes
mkdir app/Features/Workers/Factories
mkdir app/Features/Workers/Tests/Feature
mkdir app/Features/Workers/Tests/Unit

# Materials
mkdir app/Features/Materials/Controllers
mkdir app/Features/Materials/Services
mkdir app/Features/Materials/Models
mkdir app/Features/Materials/Policies
mkdir app/Features/Materials/Requests
mkdir app/Features/Materials/Resources
mkdir app/Features/Materials/Routes
mkdir app/Features/Materials/Factories
mkdir app/Features/Materials/Tests/Feature
mkdir app/Features/Materials/Tests/Unit

# Locations
mkdir app/Features/Locations/Controllers
mkdir app/Features/Locations/Services
mkdir app/Features/Locations/Models
mkdir app/Features/Locations/Policies
mkdir app/Features/Locations/Requests
mkdir app/Features/Locations/Resources
mkdir app/Features/Locations/Routes
mkdir app/Features/Locations/Factories
mkdir app/Features/Locations/Tests/Feature
mkdir app/Features/Locations/Tests/Unit

# ServiceTypes
mkdir app/Features/ServiceTypes/Controllers
mkdir app/Features/ServiceTypes/Services
mkdir app/Features/ServiceTypes/Models
mkdir app/Features/ServiceTypes/Policies
mkdir app/Features/ServiceTypes/Requests
mkdir app/Features/ServiceTypes/Resources
mkdir app/Features/ServiceTypes/Routes
mkdir app/Features/ServiceTypes/Factories
mkdir app/Features/ServiceTypes/Tests/Feature
mkdir app/Features/ServiceTypes/Tests/Unit

# Admin
mkdir app/Features/Admin/Controllers
mkdir app/Features/Admin/Services
mkdir app/Features/Admin/Policies
mkdir app/Features/Admin/Requests
mkdir app/Features/Admin/Resources
mkdir app/Features/Admin/Routes
mkdir app/Features/Admin/Tests/Feature
mkdir app/Features/Admin/Tests/Unit

# Export
mkdir app/Features/Export/Controllers
mkdir app/Features/Export/Services
mkdir app/Features/Export/Requests
mkdir app/Features/Export/Routes
mkdir app/Features/Export/Tests/Feature
mkdir app/Features/Export/Tests/Unit

# Settings
mkdir app/Features/Settings/Controllers
mkdir app/Features/Settings/Services
mkdir app/Features/Settings/Requests
mkdir app/Features/Settings/Routes
mkdir app/Features/Settings/Tests/Feature
mkdir app/Features/Settings/Tests/Unit

# Notifications
mkdir app/Features/Notifications/Services
mkdir app/Features/Notifications/Mail
```

---

## 3. Estrutura app/Core/

```powershell
# Services
mkdir app/Core/Services

# Traits
mkdir app/Core/Traits

# Enums
mkdir app/Core/Enums

# Helpers
mkdir app/Core/Helpers

# Policies
mkdir app/Core/Policies

# Middleware
mkdir app/Core/Middleware
```

---

## 4. Estrutura app/Shared/

```powershell
# Models
mkdir app/Shared/Models

# Services
mkdir app/Shared/Services
```

---

## 5. Estrutura database/

```powershell
# Factories
mkdir database/factories

# Seeders
mkdir database/seeders

# Migrations (já existe, mas confirmamos)
mkdir database/migrations
```

---

## 6. Estrutura routes/

```powershell
# API routes
mkdir routes/api
```

---

## 7. Estrutura resources/js/

```powershell
# Features
mkdir resources/js/Features/Authentication/Pages
mkdir resources/js/Features/Authentication/Components
mkdir resources/js/Features/Authentication/composables

mkdir resources/js/Features/Clients/Pages
mkdir resources/js/Features/Clients/Components
mkdir resources/js/Features/Clients/composables

mkdir resources/js/Features/Tasks/Pages
mkdir resources/js/Features/Tasks/Components
mkdir resources/js/Features/Tasks/composables

mkdir resources/js/Features/MiniTasks/Pages
mkdir resources/js/Features/MiniTasks/Components
mkdir resources/js/Features/MiniTasks/composables

mkdir resources/js/Features/WorkLogs/Pages
mkdir resources/js/Features/WorkLogs/Components
mkdir resources/js/Features/WorkLogs/composables

mkdir resources/js/Features/Sectors/Pages
mkdir resources/js/Features/Sectors/Components
mkdir resources/js/Features/Sectors/composables

mkdir resources/js/Features/Teams/Pages
mkdir resources/js/Features/Teams/Components
mkdir resources/js/Features/Teams/composables

mkdir resources/js/Features/Workers/Pages
mkdir resources/js/Features/Workers/Components
mkdir resources/js/Features/Workers/composables

mkdir resources/js/Features/Materials/Pages
mkdir resources/js/Features/Materials/Components
mkdir resources/js/Features/Materials/composables

mkdir resources/js/Features/ServiceOrders/Pages
mkdir resources/js/Features/ServiceOrders/Components
mkdir resources/js/Features/ServiceOrders/composables

mkdir resources/js/Features/Locations/Pages
mkdir resources/js/Features/Locations/Components
mkdir resources/js/Features/Locations/composables

mkdir resources/js/Features/ServiceTypes/Pages
mkdir resources/js/Features/ServiceTypes/Components
mkdir resources/js/Features/ServiceTypes/composables

mkdir resources/js/Features/Admin/Pages
mkdir resources/js/Features/Admin/Components

mkdir resources/js/Features/Dashboard/Pages

# Common Components
mkdir resources/js/Components/Common

# Composables
mkdir resources/js/composables

# Services
mkdir resources/js/services/api

# Stores
mkdir resources/js/stores

# Utils
mkdir resources/js/utils
```

---

## 8. Estrutura tests/

```powershell
# Feature
mkdir tests/Feature/Authentication
mkdir tests/Feature/Clients
mkdir tests/Feature/ServiceOrders
mkdir tests/Feature/Tasks
mkdir tests/Feature/MiniTasks
mkdir tests/Feature/WorkLogs
mkdir tests/Feature/Sectors
mkdir tests/Feature/Teams
mkdir tests/Feature/Workers
mkdir tests/Feature/Materials
mkdir tests/Feature/Locations
mkdir tests/Feature/Admin

# Unit
mkdir tests/Unit/Services
mkdir tests/Unit/Policies
mkdir tests/Unit/Helpers
mkdir tests/Unit/Traits
```

---

## 9. Todos os Comandos de Uma Vez (Script Completo)

Cria um ficheiro `create-structure.ps1` e copia isto:

```powershell
# ==============================================
# CREATE LARAVEL PROJECT STRUCTURE
# ==============================================

Write-Host "Creating app/Features structure..." -ForegroundColor Green

$features = @(
    "Authentication", "Clients", "ServiceOrders", "Tasks", "MiniTasks", 
    "WorkLogs", "Sectors", "Teams", "Workers", "Materials", 
    "Locations", "ServiceTypes", "Admin", "Export", "Settings", "Notifications"
)

$subdirs = @(
    "Controllers", "Services", "Models", "Policies", "Requests", 
    "Resources", "Routes", "Factories", "Tests/Feature", "Tests/Unit"
)

foreach ($feature in $features) {
    foreach ($subdir in $subdirs) {
        # Skip Models for some features that don't need it
        if ($feature -in @("Admin", "Export", "Settings", "Notifications") -and $subdir -eq "Models") {
            continue
        }
        # Skip Models/Policies for Notifications
        if ($feature -eq "Notifications" -and $subdir -in @("Controllers", "Policies", "Requests", "Resources", "Routes", "Tests")) {
            continue
        }
        
        $path = "app/Features/$feature/$subdir"
        New-Item -ItemType Directory -Force -Path $path | Out-Null
        Write-Host "✓ Created $path"
    }
}

Write-Host "Creating app/Core structure..." -ForegroundColor Green
$coreDirs = @("Services", "Traits", "Enums", "Helpers", "Policies", "Middleware")
foreach ($dir in $coreDirs) {
    $path = "app/Core/$dir"
    New-Item -ItemType Directory -Force -Path $path | Out-Null
    Write-Host "✓ Created $path"
}

Write-Host "Creating app/Shared structure..." -ForegroundColor Green
$sharedDirs = @("Models", "Services")
foreach ($dir in $sharedDirs) {
    $path = "app/Shared/$dir"
    New-Item -ItemType Directory -Force -Path $path | Out-Null
    Write-Host "✓ Created $path"
}

Write-Host "Creating routes/api structure..." -ForegroundColor Green
New-Item -ItemType Directory -Force -Path "routes/api" | Out-Null
Write-Host "✓ Created routes/api"

Write-Host "Creating database structure..." -ForegroundColor Green
$dbDirs = @("factories", "migrations", "seeders")
foreach ($dir in $dbDirs) {
    $path = "database/$dir"
    New-Item -ItemType Directory -Force -Path $path | Out-Null
    Write-Host "✓ Created $path"
}

Write-Host "Creating resources/js structure..." -ForegroundColor Green
$jsFeatures = @(
    "Authentication", "Clients", "Tasks", "MiniTasks", "WorkLogs", 
    "Sectors", "Teams", "Workers", "Materials", "ServiceOrders", 
    "Locations", "ServiceTypes", "Admin", "Dashboard"
)

foreach ($feature in $jsFeatures) {
    $featureDir = "resources/js/Features/$feature"
    $subPaths = @(
        "$featureDir/Pages",
        "$featureDir/Components",
        "$featureDir/composables"
    )
    
    foreach ($path in $subPaths) {
        New-Item -ItemType Directory -Force -Path $path | Out-Null
        Write-Host "✓ Created $path"
    }
}

Write-Host "Creating resources/js/Components structure..." -ForegroundColor Green
$componentDirs = @("resources/js/Components/Common")
foreach ($path in $componentDirs) {
    New-Item -ItemType Directory -Force -Path $path | Out-Null
    Write-Host "✓ Created $path"
}

Write-Host "Creating resources/js utility structure..." -ForegroundColor Green
$jsPaths = @(
    "resources/js/composables",
    "resources/js/services/api",
    "resources/js/stores",
    "resources/js/utils"
)
foreach ($path in $jsPaths) {
    New-Item -ItemType Directory -Force -Path $path | Out-Null
    Write-Host "✓ Created $path"
}

Write-Host "Creating tests structure..." -ForegroundColor Green
$testFeatures = @(
    "Authentication", "Clients", "ServiceOrders", "Tasks", "MiniTasks", 
    "WorkLogs", "Sectors", "Teams", "Workers", "Materials", 
    "Locations", "Admin"
)

foreach ($feature in $testFeatures) {
    $featurePaths = @(
        "tests/Feature/$feature",
        "tests/Unit/$feature"
    )
    foreach ($path in $featurePaths) {
        New-Item -ItemType Directory -Force -Path $path | Out-Null
        Write-Host "✓ Created $path"
    }
}

$unitPaths = @(
    "tests/Unit/Services",
    "tests/Unit/Policies",
    "tests/Unit/Helpers",
    "tests/Unit/Traits"
)
foreach ($path in $unitPaths) {
    New-Item -ItemType Directory -Force -Path $path | Out-Null
    Write-Host "✓ Created $path"
}

Write-Host ""
Write-Host "✅ Project structure created successfully!" -ForegroundColor Green
```

Executar:
```powershell
.\create-structure.ps1
```

---

## 10. Configuração Laravel

```powershell
# Gerar APP_KEY
php artisan key:generate

# Publicar configurações (se necessário)
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Limpar caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

## 11. Database Setup

```powershell
# Criar arquivo .env com DB config
# Editar .env com teus dados MySQL

# Migrations
php artisan migrate

# Seeders
php artisan db:seed
```

---

## Ordem Recomendada

1. `composer create-project laravel/laravel splnet`
2. `cd splnet`
3. `.\create-structure.ps1` (depois de copiar o script)
4. `php artisan key:generate`
5. Editar `.env` com database config
6. Copiar `db_tables.sql` para `database/migrations/`
7. `php artisan migrate`
8. Começar a desenvolver!
