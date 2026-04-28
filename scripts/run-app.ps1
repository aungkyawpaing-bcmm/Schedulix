param(
    [int]$Port = 8000,
    [string]$ServerHost = '127.0.0.1'
)

$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $PSScriptRoot
$phpDir = Get-ChildItem 'C:\laragon\bin\php' -Directory | Sort-Object Name -Descending | Select-Object -First 1

if (-not $phpDir) {
    throw 'Laragon PHP was not found in C:\laragon\bin\php.'
}

$php = Join-Path $phpDir.FullName 'php.exe'

Set-Location $projectRoot

Write-Host "Starting WBS-Generator on http://$ServerHost`:$Port" -ForegroundColor Cyan
& $php artisan serve --host=$ServerHost --port=$Port
