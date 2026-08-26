@echo off
REM Respaldo de la base de datos Glauren (para el Programador de tareas de Windows)
cd /d "C:\Chaquetas prom"
php artisan db:backup --keep=14
