# ==============================================================================
# dsBilling Automated APK Builder & Publisher (Versi 1.1.1)
# ==============================================================================
# Usage:
#   .\publish-apk.ps1 -Target customer    (Hanya build & publish Customer APK)
#   .\publish-apk.ps1 -Target staff       (Hanya build & publish Staff APK)
#   .\publish-apk.ps1 -Target all         (Build & publish kedua aplikasi)
# ==============================================================================

param (
    [string]$Target = "all"
)

$ErrorActionPreference = "Stop"

Write-Host "=================================================" -ForegroundColor Cyan
Write-Host "  dsBilling APK Build & Publish System v1.1.1    " -ForegroundColor Cyan
Write-Host "=================================================" -ForegroundColor Cyan

$destDir = "D:\dsBilling\public\downloads"
if (-not (Test-Path $destDir)) {
    New-Item -ItemType Directory -Path $destDir -Force | Out-Null
}

# 1. Build Customer APK
if ($Target -eq "all" -or $Target -eq "customer") {
    Write-Host "`n[1/2] Building dsBilling Customer APK (Release)..." -ForegroundColor Yellow
    Set-Location "D:\dsbilling-customer"
    .\gradlew.bat assembleRelease --no-daemon

    $customerSource = "D:\dsbilling-customer\app\build\outputs\apk\release\app-release.apk"
    if (Test-Path $customerSource) {
        $destFile = "$destDir\dsbilling-customer-release.apk"
        Copy-Item -Path $customerSource -Destination $destFile -Force
        $sizeMB = [math]::Round((Get-Item $destFile).Length / 1MB, 2)
        Write-Host " [OK] Customer APK berhasil di-publish ($sizeMB MB):" -ForegroundColor Green
        Write-Host "      -> $destFile" -ForegroundColor Green
    } else {
        Write-Error "Customer APK release build output not found!"
    }
}

# 2. Build Staff / Mobile APK
if ($Target -eq "all" -or $Target -eq "staff") {
    Write-Host "`n[2/2] Checking dsBilling Staff / Mobile APK (Release)..." -ForegroundColor Yellow
    $staffSource = "D:\dsbilling-mobile\app\build\outputs\apk\release\app-release.apk"
    if (Test-Path $staffSource) {
        Copy-Item -Path $staffSource -Destination "$destDir\dsbilling-mobile-release.apk" -Force
        $sizeMB = [math]::Round((Get-Item "$destDir\dsbilling-mobile-release.apk").Length / 1MB, 2)
        Write-Host " [OK] Staff/Mobile APK berhasil di-publish ($sizeMB MB):" -ForegroundColor Green
        Write-Host "      -> $destDir\dsbilling-mobile-release.apk" -ForegroundColor Green
    } else {
        Write-Host "Staff APK source not found, trying build..." -ForegroundColor Yellow
        Set-Location "D:\dsbilling-mobile"
        .\gradlew.bat assembleRelease --no-daemon
        Copy-Item -Path $staffSource -Destination "$destDir\dsbilling-mobile-release.apk" -Force
    }
}

Set-Location "D:\dsBilling"
Write-Host "`n=================================================" -ForegroundColor Cyan
Write-Host " PUBLISH SELESAI! File APK siap diunduh pengguna:" -ForegroundColor Green
Get-ChildItem -Path $destDir -Filter "*.apk" | Select-Object Name, Length, LastWriteTime | Format-Table -AutoSize
Write-Host "=================================================" -ForegroundColor Cyan
