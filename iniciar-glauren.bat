@echo off
title Glauren - Servidor local
echo ============================================
echo   GLAUREN - Iniciando plataforma local
echo ============================================
echo.

REM 1) Arranca MySQL si no esta corriendo
tasklist /FI "IMAGENAME eq mysqld.exe" | find /I "mysqld.exe" >nul
if errorlevel 1 (
    echo [1/3] Iniciando MySQL...
    start "MySQL" /B "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini"
    timeout /t 5 /nobreak >nul
) else (
    echo [1/3] MySQL ya estaba corriendo.
)

REM 2) Respaldo de la base de datos
echo [2/3] Creando respaldo de la base de datos...
cd /d "C:\Chaquetas prom"
php artisan db:backup --keep=14

REM 3) Arranca el servidor web y abre el navegador
echo [3/3] Iniciando el sitio en http://127.0.0.1:8000
start "" http://127.0.0.1:8000
php artisan serve --host=127.0.0.1 --port=8000

pause
