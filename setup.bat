@echo off
setlocal
cd /d "%~dp0"
where php >nul 2>nul || (echo Herd PHP topilmadi. Herd terminali yoki VS Code terminalidan qayta urinib ko'ring. & pause & exit /b 1)
where composer >nul 2>nul || (echo Composer topilmadi. Composer o'rnating yoki Herd terminalidan ishga tushiring. & pause & exit /b 1)
if not exist .env copy .env.example .env >nul
echo .env faylidagi PostgreSQL DB_PASSWORD qiymatini tekshiring.
echo PostgreSQL serveri ishlab turgani va vermont_shop bazasi yaratilganini tekshiring.
pause
composer install
if errorlevel 1 (echo Composer paketlari o'rnatilmadi. & pause & exit /b 1)
php artisan key:generate
if errorlevel 1 (echo .env sozlamasini tekshiring. & pause & exit /b 1)
php artisan migrate --seed
if errorlevel 1 (echo PostgreSQL ulanishini va pdo_pgsql kengaytmasini tekshiring. & pause & exit /b 1)
php artisan storage:link
echo Tayyor. Endi Herd ko'rsatgan vermontshop.test manzilini brauzerda oching.
pause
endlocal
