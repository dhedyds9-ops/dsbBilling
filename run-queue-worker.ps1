# dsBilling Laravel Queue Worker - Windows Task Scheduler Runner
# File: D:\dsBilling\run-queue-worker.ps1
# Jalankan semua queue yang dibutuhkan dsBilling dalam satu proses

$projectPath = "D:\dsBilling"
$phpPath = (Get-Command php -ErrorAction SilentlyContinue).Source

if (-not $phpPath) {
    Write-Error "PHP not found in PATH. Please ensure PHP is installed and in PATH."
    exit 1
}

Set-Location $projectPath

Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] Starting dsBilling Queue Worker..."

& $phpPath artisan queue:work `
    --queue=monitoring-router,default,provisioning,radius-coa,notifications-wa `
    --sleep=3 `
    --tries=3 `
    --timeout=120 `
    --max-time=3600

Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] Queue Worker exited. Will be restarted by Task Scheduler."
