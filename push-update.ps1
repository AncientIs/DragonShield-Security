# push-update.ps1 - Automated Git Push for DragonShield-Security
param([string]$Message = '')
$RepoPath = $PSScriptRoot
if (-not $RepoPath) { $RepoPath = Get-Location }
$Branch = 'main'
$Remote = 'origin'
Write-Host '====== DragonShield-Security Auto Push ======' -ForegroundColor Cyan
Set-Location $RepoPath
Write-Host "[INFO] Repo: $RepoPath" -ForegroundColor Yellow
if (-not (Test-Path '.git')) { Write-Host '[ERROR] Not a git repo!' -ForegroundColor Red; exit 1 }
Write-Host '[STEP 1] Checking status...' -ForegroundColor Green
git status --short
$changes = git status --porcelain
if ([string]::IsNullOrWhiteSpace($changes)) { Write-Host '[INFO] No changes to commit.' -ForegroundColor Yellow; exit 0 }
Write-Host '[STEP 2] Staging all changes...' -ForegroundColor Green
git add -A
$ts = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
if ([string]::IsNullOrWhiteSpace($Message)) { $Message = "Auto-update: $ts" } else { $Message = "$Message [$ts]" }
Write-Host "[STEP 3] Committing: $Message" -ForegroundColor Green
git commit -m $Message
Write-Host "[STEP 4] Pushing to $Remote/$Branch..." -ForegroundColor Green
git push $Remote $Branch
if ($LASTEXITCODE -eq 0) { Write-Host '=== Push completed successfully! ===' -ForegroundColor Green } else { Write-Host '[ERROR] Push failed.' -ForegroundColor Red }
Write-Host 'Press any key to exit...'; $null = $Host.UI.RawUI.ReadKey('NoEcho,IncludeKeyDown')
