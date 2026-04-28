param(
    [switch]$SkipComposerInstall,
    [switch]$SkipNodeInstall,
    [switch]$SkipBuild,
    [switch]$SkipSeed
)

$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $PSScriptRoot
$phpDir = Get-ChildItem 'C:\laragon\bin\php' -Directory | Sort-Object Name -Descending | Select-Object -First 1
$nodeDir = Get-ChildItem 'C:\laragon\bin\nodejs' -Directory | Sort-Object Name -Descending | Select-Object -First 1

if (-not $phpDir) {
    throw 'Laragon PHP was not found in C:\laragon\bin\php.'
}

if (-not $nodeDir) {
    throw 'Laragon Node.js was not found in C:\laragon\bin\nodejs.'
}

$php = Join-Path $phpDir.FullName 'php.exe'
$composer = 'C:\laragon\bin\composer\composer.phar'
$npm = Join-Path $nodeDir.FullName 'npm.cmd'
$sqliteFile = Join-Path $projectRoot 'storage\app\wbs-generator.sqlite'

Set-Location $projectRoot

if (-not (Test-Path '.env')) {
    Copy-Item '.env.example' '.env'
}

if (-not (Test-Path $sqliteFile)) {
    New-Item -ItemType File -Path $sqliteFile -Force | Out-Null
}

if (-not $SkipComposerInstall) {
    & $php $composer install
}

if (-not $SkipNodeInstall) {
    & $npm install --cache .npm-cache --prefer-online --no-audit
}

& $php artisan key:generate --force
& $php artisan config:clear

if ($SkipSeed) {
    & $php artisan migrate
} else {
    & $php artisan migrate:fresh --seed
}

if (-not $SkipBuild) {
    & $npm run build
}

Write-Host ''
Write-Host 'WBS-Generator local setup is ready.' -ForegroundColor Green
Write-Host 'You can now run .\scripts\run-app.ps1 or .\scripts\run-dev.ps1'
