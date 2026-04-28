# WBS-Generator

Laravel-based WBS project management system built from `UNIFIED_WBS_PROJECT_SPEC.md`.

## Quick Start

Run these from `C:\laragon\www\bcmm-wbs\WBS-Generator`.

### 1. Prepare the app

```powershell
Set-ExecutionPolicy -Scope Process Bypass
.\scripts\setup-local.ps1
```

This script will:

- install Composer dependencies
- install npm dependencies
- create the local SQLite file used by the current `.env`
- generate the app key
- run migrations and seed demo data
- build frontend assets

### 2. Run the app

```powershell
.\scripts\run-app.ps1
```

Open:

```text
http://127.0.0.1:8000
```

### 3. Run in dev mode

```powershell
.\scripts\run-dev.ps1
```

This opens:

- one PowerShell window for `php artisan serve`
- one PowerShell window for `npm run dev`

## Seeded Logins

- `owner@wbs-generator.test` / `password`
- `pm@wbs-generator.test` / `password`

## Useful Variants

Skip dependency reinstalls:

```powershell
.\scripts\setup-local.ps1 -SkipComposerInstall -SkipNodeInstall
```

Skip rebuilding frontend assets:

```powershell
.\scripts\setup-local.ps1 -SkipBuild
```

Run migrations without reseeding:

```powershell
.\scripts\setup-local.ps1 -SkipSeed
```

Run tests:

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test
```

## Current Local Database Note

- `.env.example` is prepared for Laragon MySQL.
- The current working local `.env` uses SQLite so the app can run immediately in this workspace.

## Build Notes

Progress notes live in [BUILD_NOTES.md](C:/laragon/www/bcmm-wbs/WBS-Generator/BUILD_NOTES.md).
