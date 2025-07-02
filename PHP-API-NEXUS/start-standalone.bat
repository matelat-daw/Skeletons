@echo off
echo Iniciando servidor PHP standalone en puerto 9000...
echo Para detener, presiona Ctrl+C
echo.
cd /d "C:\Server\www\Skeletons\PHP-API-NEXUS"
php -S localhost:9000 standalone-server.php
pause
