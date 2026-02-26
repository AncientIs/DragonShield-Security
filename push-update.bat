@echo off
REM push-update.bat - Double-click to push updates to DragonShield-Security
REM This batch file wraps push-update.ps1 for easy execution
cd /d "%~dp0"
powershell -ExecutionPolicy Bypass -File "%~dp0push-update.ps1" %*
