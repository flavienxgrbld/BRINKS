@echo off
chcp 65001 >nul
echo Regeneration des fichiers backend...

cd /d "%~dp0backend"

del /q *.php 2>nul

echo Fichiers supprimes. Veuillez executer les commandes PowerShell suivantes pour recreer les fichiers:
echo.
echo cd J:\git\BRINKS
echo git checkout HEAD -- backend/
echo git status

pause
