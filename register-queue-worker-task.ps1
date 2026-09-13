# Register Laravel Queue Worker as Windows Task Scheduler Task
# Run this script once as Administrator

$taskName = "dsBilling-QueueWorker"
$projectPath = "D:\dsBilling"
$scriptPath = "$projectPath\run-queue-worker.ps1"
$phpPath = (Get-Command php -ErrorAction SilentlyContinue).Source

if (-not $phpPath) {
    Write-Error "PHP not found in PATH. Aborting."
    exit 1
}

if (Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue) {
    Write-Host "Removing existing task..."
    Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
}

$action = New-ScheduledTaskAction `
    -Execute "powershell.exe" `
    -Argument ("-NonInteractive -NoProfile -ExecutionPolicy Bypass -File `"" + $scriptPath + "`"") `
    -WorkingDirectory $projectPath

$trigger = New-ScheduledTaskTrigger -AtStartup

$settings = New-ScheduledTaskSettingsSet `
    -ExecutionTimeLimit ([TimeSpan]::Zero) `
    -RestartCount 99 `
    -RestartInterval (New-TimeSpan -Minutes 5) `
    -MultipleInstances IgnoreNew `
    -StartWhenAvailable

$principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -LogonType ServiceAccount -RunLevel Highest

Write-Host "Registering task..."
Register-ScheduledTask `
    -TaskName $taskName `
    -Action $action `
    -Trigger $trigger `
    -Settings $settings `
    -Principal $principal `
    -Description "dsBilling Laravel Queue Worker"

Write-Host "Starting task now..."
Start-ScheduledTask -TaskName $taskName

Start-Sleep 3
$state = (Get-ScheduledTask -TaskName $taskName).State
Write-Host "Task state: $state"
Write-Host "Done! Queue worker registered and started."
