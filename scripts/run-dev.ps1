param(
    [int]$Port = 8000
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
$npm = Join-Path $nodeDir.FullName 'npm.cmd'

Write-Host 'Starting Laravel server and Vite dev server...' -ForegroundColor Cyan

Start-Process powershell -ArgumentList @(
    '-NoExit',
    '-Command',
    "Set-Location '$projectRoot'; & '$php' artisan serve --host=127.0.0.1 --port=$Port"
)

Start-Process powershell -ArgumentList @(
    '-NoExit',
    '-Command',
    "Set-Location '$projectRoot'; & '$npm' run dev"
)

Write-Host "Laravel: http://127.0.0.1:$Port" -ForegroundColor Green
Write-Host 'Vite dev server started in a second window.' -ForegroundColor Green
